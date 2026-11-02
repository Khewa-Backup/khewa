#!/bin/sh
# Clears PrestaShop var/cache when the account's file usage (inodes) exceeds LIMIT.
# Cron: */30 * * * * /bin/sh $HOME/clear_ps_cache.sh

LIMIT=550000
CACHE_DIR="$HOME/public_html/var/cache"
LOG="$HOME/ps_cache_clean.log"
LOCK="$HOME/.ps_cache_clean.lock"

# Skip if a previous run is still going
mkdir "$LOCK" 2>/dev/null || exit 0
trap 'rmdir "$LOCK"' EXIT

# Same number cPanel shows as "File Usage": every file + folder in the account
COUNT=$(find "$HOME" -xdev 2>/dev/null | wc -l | tr -d ' ')
NOW=$(date '+%Y-%m-%d %H:%M:%S')

if [ "$COUNT" -gt "$LIMIT" ] && [ -d "$CACHE_DIR" ]; then
    # Rename first (instant), recreate empty cache, then delete the old copy
    OLD="$CACHE_DIR.old.$$"
    mv "$CACHE_DIR" "$OLD" && mkdir -p "$CACHE_DIR" && nice -n 19 rm -rf "$OLD"
    AFTER=$(find "$HOME" -xdev 2>/dev/null | wc -l | tr -d ' ')
    echo "$NOW files=$COUNT > $LIMIT, cache cleared, now=$AFTER" >> "$LOG"
else
    echo "$NOW files=$COUNT ok" >> "$LOG"
fi
