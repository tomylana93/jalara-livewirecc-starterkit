# Commit Message Instructions

Generate exactly one commit message using Conventional Commits.

Format:

<type>(<scope>): <description>

Use English.

Allowed types:
feat, fix, refactor, perf, docs, test, style, build, ci, chore, revert

Scope rules:

Prefer a business or feature scope:
auth, user, customer, shipment, pickup, tracking, invoice, payment, report, notification

Use a technical scope only when the change is not tied to a specific feature:
api, database, ui, config, deps, vite, actions

Omit the scope if no useful scope can be determined.

Subject rules:

- Use imperative mood.
- Start with a lowercase verb.
- Be concise and specific.
- Describe the actual change.
- Do not end with a period.
- Do not use generic descriptions such as:
  "update files"
  "improve code"
  "various changes"
  "code improvements"
  "fix issues"
  "refactor code"

Prefer:
fix(shipment): prevent duplicate airway bill creation

Instead of:
fix: fix shipment issue

Prefer:
refactor(customer): extract address validation

Instead of:
refactor: improve code structure

When multiple files are changed, describe the primary logical change rather than listing files.