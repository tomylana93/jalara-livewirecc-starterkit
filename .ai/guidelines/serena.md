# Serena Semantic Navigation and Refactoring

Use Serena's semantic tools instead of reading whole files when exploring or editing PHP and JavaScript code. Active language servers: `typescript`, `php_phpantom`. Line numbers from Serena tools are 0-based.

## Navigation (prefer over full-file reads)

- Start new files with `get_symbols_overview` for a symbol outline.
- Use `find_symbol` with `depth > 0` to list children (e.g. class methods), then re-query with `include_body=True` only for the symbols you need.
- Use `find_declaration` to jump from a usage to its declaration.
- Use `find_referencing_symbols` to map callers before changing a symbol's signature or behavior.
- Use `find_implementations` for interface/abstract method implementations.
- Use `search_for_pattern` only when the symbol name or location is unknown; then continue with symbolic tools.
- Use `get_diagnostics_for_file` to check errors for a touched file.
- Once a full file is read, do not re-analyze it with symbolic tools.

## Refactoring (prefer over hand-edits)

- Prefer `rename_symbol` and `safe_delete_symbol`: they are reference-aware across declarations, references, overrides, and imports. On success the refactor is complete; do not re-read files just to confirm propagation.
- Replace a whole symbol via `replace_symbol_body`; insert code via `insert_after_symbol` / `insert_before_symbol`.
- For partial edits inside a symbol, use a targeted file edit with a narrowly scoped match.
- For one edit across many files, use `replace_in_files` in dry-run mode first, then apply the selected occurrences with a count guard.
- Batch independent Serena calls in parallel; chain sequentially only on real dependencies.

## Boundaries

- Keep edits backward-compatible or update all references found via `find_referencing_symbols`.
- Do not use Serena memory tools in this project.

## Sibling repositories

- The Vue reference is `../jalara-vue-starterkit`; the React reference is `../jalara-react-starterkit`.
- Activate each repository explicitly before using its symbol tools. Additional workspace folders support cross-repository references but do not index sibling symbols.
- Restore `jalara-livewirecc-starterkit` as the active project before editing this repository.
- Adapt shared backend behavior and tooling to Blade, Livewire, and Flux. Preserve each sibling repository unless the user asks to change it.
- Use targeted pattern searches for Blade templates whose embedded PHP is not indexed by the language server.
