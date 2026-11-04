#!/bin/bash
# Move the live shop's data into the local 9.2 shop.
# Usage:  bash merge.sh /path/to/live-dump.sql.gz [label]
#   label is used in the backup file name (default: today's date).
# What it does (see ../data-move-log.md for the reasons):
#   1. backs up the local database khewa_live
#   2. loads the live dump into a spare database khewa_cutover
#   3. replaces every DATA table in khewa_live with the live one (columns matched by name)
#   4. runs post-fixes.sql (the data changes the 1.7 -> 9.2 update makes, plus our own data fixes)
#   5. rebuilds the filter index (amazzingfilter) and empties the cache
#   6. runs the checks (row counts, newest rows, totals) and the security scan
set -euo pipefail

DUMP=${1:?give the live dump .sql.gz}
LABEL=${2:-$(date +%Y-%m-%d)}
HERE=$(cd "$(dirname "$0")" && pwd)
LIVE=$(cd "$HERE/../.." && pwd)            # .../khewa-updates/live
BACKUPS=$(cd "$LIVE/../backups" && pwd)
SRC=khewa_cutover
DST=khewa_live
OUT="$HERE/out-$LABEL"; mkdir -p "$OUT"
BIN=/Applications/AMPPS/apps/mysql/bin
CNF=$(mktemp); trap 'rm -f "$CNF"' EXIT
printf '[client]\nuser=root\npassword=mysql\nmax_allowed_packet=1G\n' > "$CNF"
m()  { "$BIN/mysql" --defaults-extra-file="$CNF" "$@"; }
step() { echo; echo "== $(date +%H:%M:%S) $*"; }

# Tables on the DATA list that are handled differently:
#   kept from local: feature flags are 9.2 setup; the cart rule type tables are new in 9.1 (not on live)
KEEP_LOCAL=" ps_feature_flag ps_cart_rule_type ps_cart_rule_type_lang "
#   rebuilt in post-fixes.sql (new in 8.1, not on live)
REBUILT=" ps_product_attribute_lang "

step "1. backup of $DST"
BK="$BACKUPS/khewa_live-before-merge-$LABEL.sql.gz"
if [ -s "$BK" ]; then echo "backup already there, kept (never overwritten): $BK"; else
"$BIN/mysqldump" --defaults-extra-file="$CNF" --single-transaction --quick --routines --triggers \
  --no-tablespaces --set-gtid-purged=OFF --column-statistics=0 --hex-blob $DST \
  | gzip > "$BK"; fi
ls -lh "$BK"

step "2. load the live dump into $SRC"
m -e "DROP DATABASE IF EXISTS $SRC; CREATE DATABASE $SRC DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"
gzcat "$DUMP" | m $SRC
m -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$SRC'" | sed 's/^/tables in live dump: /'
m -N $SRC -e "SELECT value FROM ps_configuration WHERE name='PS_VERSION_DB'" | sed 's/^/live version: /'

step "3. replace the DATA tables"
awk '/^## /{s=$2} s=="DATA" && /^(ps_|zz)/ {print $1}' "$LIVE/khewa-docs/data-move-tables.txt" > "$OUT/data-tables.txt"
: > "$OUT/copy.log"
while read -r T; do
  case "$KEEP_LOCAL$REBUILT" in *" $T "*) echo "$T: skipped (see list)" >> "$OUT/copy.log"; continue;; esac
  INSRC=$(m -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$SRC' AND table_name='$T'")
  if [ "$INSRC" = 0 ]; then
    m $DST -e "SET foreign_key_checks=0; TRUNCATE \`$T\`;"
    echo "$T: not on live, emptied" >> "$OUT/copy.log"; continue
  fi
  COLS=$(m -N -e "SET SESSION group_concat_max_len=1000000; SELECT GROUP_CONCAT(CONCAT('\`',d.column_name,'\`') ORDER BY d.ordinal_position)
    FROM information_schema.columns d JOIN information_schema.columns s
      ON s.table_schema='$SRC' AND s.table_name=d.table_name AND s.column_name=d.column_name
    WHERE d.table_schema='$DST' AND d.table_name='$T'")
  m $DST -e "SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO'; SET foreign_key_checks=0; SET unique_checks=0;
    TRUNCATE \`$T\`; INSERT INTO \`$T\` ($COLS) SELECT $COLS FROM $SRC.\`$T\`;"
  echo "$T: copied" >> "$OUT/copy.log"
done < "$OUT/data-tables.txt"
grep -c copied "$OUT/copy.log" | sed 's/^/tables copied: /'

step "4. post-fixes.sql"
m -v $DST < "$HERE/post-fixes.sql" > "$OUT/post-fixes.out"
tail -5 "$OUT/post-fixes.out"

step "5. filter index + cache"
/opt/homebrew/opt/php/bin/php -d memory_limit=2G "$HERE/reindex-filter.php" "$LIVE"
rm -rf "$LIVE/var/cache/"*

step "6. checks"
bash "$HERE/checks.sh" "$OUT" | tee "$OUT/checks.txt"
echo; echo "done. Output in $OUT"
