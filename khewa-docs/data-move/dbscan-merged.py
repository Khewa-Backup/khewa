#!/usr/bin/env python3
# Security scan of the DATA tables after the move (same patterns as the 3 Nov scan).
# Prints every table/column/pattern with hits. On 3 Nov the clean local copy had none in DATA tables
# except ps_log.message b64blob (1 row, a module's log text).
# Usage: python3 -I dbscan-merged.py <out folder with data-tables.txt>
import os, subprocess, sys, tempfile

out = sys.argv[1]
tables = [t.strip() for t in open(os.path.join(out, 'data-tables.txt')) if t.strip()]
cnf = tempfile.NamedTemporaryFile('w', delete=False)
cnf.write('[client]\nuser=root\npassword=mysql\n'); cnf.close()
MYSQL = ['/Applications/AMPPS/apps/mysql/bin/mysql', '--defaults-extra-file=' + cnf.name, '-N', '-B', 'khewa_live']
SKIP = {'ps_connections', 'ps_guest', 'ps_pagenotfound', 'ps_connections_source', 'ps_search_word', 'ps_pos_search_index'}
PATS = {
    'script': "LIKE '%<script%'",
    'eval': "REGEXP 'eval[[:space:]]*\\\\('",
    'atob': "LIKE '%atob(%'",
    'fromCharCode': "LIKE '%fromCharCode%'",
    'iframe': "LIKE '%<iframe%'",
    '_0x': "REGEXP '_0x[0-9a-f]{4}'",
    'eth': "REGEXP 'eth_call|blxrbdn|bsc-dataseed|web3|4f3d0c43'",
    'php': "LIKE '%<?php%'",
    'b64blob': "REGEXP '[A-Za-z0-9+/]{300,}'",
    'onerror': "REGEXP 'onerror[[:space:]]*=|onload[[:space:]]*=|javascript:'",
    'ENT_SUB': "LIKE '%ENT_SUBSTITUTE%'",
}

def run(q):
    r = subprocess.run(MYSQL + ['-e', q], capture_output=True, text=True)
    return r.returncode, r.stdout.strip(), r.stderr.strip()

inlist = ','.join("'%s'" % t for t in tables if t not in SKIP)
_, cols_txt, _ = run("SELECT table_name, column_name FROM information_schema.columns WHERE table_schema='khewa_live' "
                     "AND table_name IN (%s) AND data_type IN ('char','varchar','text','mediumtext','longtext','tinytext')" % inlist)
bytab = {}
for line in cols_txt.splitlines():
    t, c = line.split('\t')
    bytab.setdefault(t, []).append(c)

hits = []
for t, cs in sorted(bytab.items()):
    sel = ['SUM(`%s` %s)' % (c, p) for c in cs for p in PATS.values()]
    rc, o, e = run('SELECT %s FROM `%s`' % (','.join(sel), t))
    if rc:
        hits.append('ERR\t%s\t%s' % (t, e.splitlines()[-1][:150])); continue
    vals = o.split('\t'); i = 0
    for c in cs:
        for k in PATS:
            v = vals[i]; i += 1
            if v not in ('0', 'NULL', ''):
                hits.append('%s\t%s\t%s\t%s' % (t, c, k, v))
os.unlink(cnf.name)
open(os.path.join(out, 'dbscan-hits.tsv'), 'w').write('\n'.join(hits) + '\n')
print('\n'.join('  ' + h for h in hits) if hits else '  no hits')
print('  (%d tables, %d text columns scanned)' % (len(bytab), sum(len(v) for v in bytab.values())))
