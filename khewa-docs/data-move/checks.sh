#!/bin/bash
# Checks after the data move: live (khewa_cutover) against the merged shop (khewa_live).
# Usage: bash checks.sh <out folder with data-tables.txt>
OUT=${1:?out folder}
HERE=$(cd "$(dirname "$0")" && pwd)
BIN=/Applications/AMPPS/apps/mysql/bin
CNF=$(mktemp); trap 'rm -f "$CNF"' EXIT
printf '[client]\nuser=root\npassword=mysql\n' > "$CNF"
m() { "$BIN/mysql" --defaults-extra-file="$CNF" -N "$@"; }

echo "-- row counts that differ (table, live, merged):"
BAD=0
while read -r T; do
  case " ps_feature_flag ps_cart_rule_type ps_cart_rule_type_lang ps_product_attribute_lang ps_cart_rule_compatible_types " in *" $T "*) continue;; esac
  L=$(m -e "SELECT COUNT(*) FROM khewa_cutover.\`$T\`" 2>/dev/null || echo missing)
  D=$(m -e "SELECT COUNT(*) FROM khewa_live.\`$T\`")
  [ "$L" != "$D" ] && { echo "  $T $L $D"; BAD=1; }
done < "$OUT/data-tables.txt"
[ $BAD = 0 ] && echo "  none: every data table has the same row count as live"

echo "-- newest rows (live | merged):"
for q in "MAX(id_order) FROM %s.ps_orders" "MAX(date_add) FROM %s.ps_orders" "MAX(id_customer) FROM %s.ps_customer" \
         "MAX(id_product) FROM %s.ps_product" "MAX(id_cart) FROM %s.ps_cart" "MAX(id_cart_rule) FROM %s.ps_cart_rule" \
         "MAX(id_pos_order) FROM %s.ps_pos_orders" "MAX(number) FROM %s.ps_order_invoice"; do
  a=$(m -e "SELECT $(printf "$q" khewa_cutover)" 2>&1); b=$(m -e "SELECT $(printf "$q" khewa_live)" 2>&1)
  echo "  ${q%% FROM*} ${q##*.}: $a | $b"
done

echo "-- order totals for the last 7 days (live | merged):"
for db in khewa_cutover khewa_live; do
  m -e "SELECT '$db', COUNT(*), ROUND(SUM(total_paid_tax_incl),2) FROM $db.ps_orders WHERE date_add >= (SELECT MAX(date_add) FROM $db.ps_orders) - INTERVAL 7 DAY"
done

echo "-- stock and price, 10 random products (rows where live and merged differ):"
m -e "SELECT s.id_product, s.id_product_attribute, s.quantity, d.quantity FROM khewa_cutover.ps_stock_available s
  JOIN khewa_live.ps_stock_available d USING (id_stock_available) WHERE s.quantity <> d.quantity LIMIT 10"
m -e "SELECT s.id_product, s.price, d.price FROM khewa_cutover.ps_product_shop s JOIN khewa_live.ps_product_shop d
  USING (id_product, id_shop) WHERE s.price <> d.price LIMIT 10"

echo "-- gift cards / vouchers: remaining amount and uses differ:"
m -e "SELECT s.id_cart_rule, s.quantity, d.quantity, s.reduction_amount, d.reduction_amount FROM khewa_cutover.ps_cart_rule s
  JOIN khewa_live.ps_cart_rule d USING (id_cart_rule) WHERE NOT (s.quantity <=> d.quantity) OR s.reduction_amount <> d.reduction_amount LIMIT 10"

echo "-- data that points at setup rows that don't exist here:"
m khewa_live -e "
SELECT 'orders.current_state', COUNT(*) FROM ps_orders o WHERE o.current_state NOT IN (SELECT id_order_state FROM ps_order_state) AND o.current_state<>0;
SELECT 'order_history.state', COUNT(*) FROM ps_order_history h WHERE h.id_order_state NOT IN (SELECT id_order_state FROM ps_order_state);
SELECT 'orders.id_carrier', COUNT(*) FROM ps_orders o WHERE o.id_carrier NOT IN (SELECT id_carrier FROM ps_carrier) AND o.id_carrier<>0;
SELECT 'orders.id_currency', COUNT(*) FROM ps_orders o WHERE o.id_currency NOT IN (SELECT id_currency FROM ps_currency);
SELECT 'customer.id_default_group', COUNT(*) FROM ps_customer c WHERE c.id_default_group NOT IN (SELECT id_group FROM ps_group);
SELECT 'address.id_country', COUNT(*) FROM ps_address a WHERE a.id_country NOT IN (SELECT id_country FROM ps_country);
SELECT 'product.id_tax_rules_group', COUNT(*) FROM ps_product p WHERE p.id_tax_rules_group NOT IN (SELECT id_tax_rules_group FROM ps_tax_rules_group) AND p.id_tax_rules_group<>0;
SELECT 'cart.id_carrier', COUNT(*) FROM ps_cart c WHERE c.id_carrier NOT IN (SELECT id_carrier FROM ps_carrier) AND c.id_carrier<>0;
SELECT 'image_shop without image', COUNT(*) FROM ps_image_shop s WHERE s.id_image NOT IN (SELECT id_image FROM ps_image);
"

echo "-- new admin accounts or modules on live since our copy (should be none):"
m -e "SELECT 'employee', id_employee, email FROM khewa_live.ps_employee WHERE id_employee NOT IN (1,2)"
m -e "SELECT 'module only on live', name, version FROM khewa_cutover.ps_module WHERE name NOT IN (SELECT name FROM khewa_live.ps_module)"

echo "-- security scan of the merged data (hits not seen on 3 Nov):"
/usr/bin/python3 -I "$HERE/dbscan-merged.py" "$OUT"
