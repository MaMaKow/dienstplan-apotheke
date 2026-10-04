# Legacy SQL fixtures

Eingefrorener Stand von `src/sql/` **vor** Commit `51ac961`
(`git rev-parse 51ac961^` → `2f628ba`), aufgenommen am 2026-09-24.

Diese Dateien werden **nicht gepflegt** und **nicht geändert**.
Sie dienen als Ausgangszustand für `tests/Integration/DatabaseMigrationTest.php`,
um nachzuweisen, dass die Migrationen in
`src/php/classes/class.update_database.php` ein altes Schema
verlustfrei auf den aktuellen Stand bringen.

Neu erzeugen (falls nötig):
    git archive 51ac961^ src/sql | tar -x -C /tmp
    rm -rf tests/fixtures/legacy-sql
    mkdir -p tests/fixtures/legacy-sql
    mv /tmp/src/sql/* tests/fixtures/legacy-sql/
    rm -rf /tmp/src
    # README.md danach wieder anlegen (nicht im Archiv enthalten)

Hinweis: Dateinamen und Schreibweise (z. B. `dienstplan.sql`,
`öffnungszeiten.sql`) entsprechen dem historischen Stand und weichen
absichtlich vom heutigen `src/sql/` ab.
