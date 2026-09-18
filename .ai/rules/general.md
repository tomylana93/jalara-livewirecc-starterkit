---
paths:
  - '**'
---

# General

## Use Conventional Commits for commit messages
Use Conventional Commits in English: <type>(<scope>): <description>. Prefer a business scope; use a technical scope only when not tied to a feature, and omit scope when unclear. Use a concise imperative lowercase subject without a final period. See .github/instructions/commit-message.instructions.md for examples.

## Composer ci:check is the final gate
Run composer ci:check after edits and narrow tests pass, before reporting done, committing, or creating a PR. Install dependencies first when missing. Fix failures and rerun the complete gate until green; partial checks do not replace it. Report actual failures rather than claiming success.
