---
name: implement
description: Use when starting any feature, bugfix, refactor, or behavior change in this project — before brainstorming, exploring code, or writing any code. Also use when tempted to "just quickly fix" something.
---

# Implement: KISSJ Development Cycle

## Overview

The mandatory development cycle for every code change in this project. Each phase gates the next — no phase may be skipped, reordered, or merged into another.

**Violating the letter of this cycle is violating its spirit.**

## The Cycle

Execute phases in order. Announce each phase as you enter it.

### 1. Brainstorm
**REQUIRED SUB-SKILL:** superpowers:brainstorming
Explore intent, requirements, and design with the user before anything else.

### 2. Grill — NOT optional
**REQUIRED SUB-SKILL:** grill-me
Immediately after brainstorming converges on a design, invoke grill-me and interview the user until every branch of the decision tree is resolved. This is a mandatory gate, not an escalation the user must request.

- Do NOT skip because the change is "simple" — simple changes hide the assumptions grilling exposes.
- Do NOT skip because the user seems in a hurry or already confident.
- Do NOT substitute a couple of your own clarifying questions for the skill.
- Only an explicit waiver of the grilling from the user — in any wording ("skip the grilling", "no need to grill me", …) — exempts this phase; note the skip in the plan. Urgency, confidence, or a detailed spec is not a waiver.

### 3. Plan
**REQUIRED SUB-SKILL:** superpowers:writing-plans
Write the implementation plan from the grilled, agreed design.

### 4. Isolate
**REQUIRED SUB-SKILL:** superpowers:using-git-worktrees
Ensure an isolated workspace before executing the plan.

Worktrees MUST live at `.worktrees/<branch>` under the repo root — nowhere else. The dev container mounts only the repo root (`/var/www/html`); a worktree outside it is invisible to the quality gate.

After creating the worktree, install its dependencies through the container:

```bash
docker exec -u 1000 -w /var/www/html/.worktrees/<branch> kissj-app-php-fpm-1 composer install
```

### 5. Execute
**REQUIRED SUB-SKILL:** superpowers:subagent-driven-development
Every task follows superpowers:test-driven-development — test first, watch it fail, then implement. Bugs found along the way go through superpowers:systematic-debugging before fixing.

### 6. Quality gate
Run the full suite from the checkout you are working in — it must pass clean:

```bash
# main workspace
docker exec -u 1000 kissj-app-php-fpm-1 composer test   # PHPStan level 10 + CS Fixer + PHPUnit
# worktree
docker exec -u 1000 -w /var/www/html/.worktrees/<branch> kissj-app-php-fpm-1 composer test
```

No new PHPStan ignores or baseline entries. Then apply superpowers:verification-before-completion.

If you are in a worktree:
- Never run `composer phinx:migrate` — the shared dev database belongs to the main workspace; migrations are exercised by the sqlite test bootstrap.
- Runtime (browser) verification cannot happen here — the running app serves the main workspace only. Do not improvise ways to serve or execute the worktree at runtime (second server, throwaway CLI entrypoints). Note the deferral in the plan; it runs in phase 7b.

If you are in the main workspace: for changes with a runtime surface, use the verify skill to exercise the change end-to-end.

### 7. Code review — automated, then manual

**7a. Automated (in the worktree):**
**REQUIRED SUB-SKILL:** superpowers:requesting-code-review
Additionally run the code-review skill on the diff. Handle feedback via superpowers:receiving-code-review.

**Fix-all loop:** fixing quality gate failures and code review findings is mechanical — fix every one, do not triage, defer, or ask which to address. Any code change made after phase 6 re-enters the cycle at phase 6: re-run the quality gate, then re-review the changed code. Loop until both phases pass with nothing left to fix.

**7b. Manual (in the main workspace):**
When 7a is clean, ask the user: "Ready for manual review — can the main workspace take `<branch>`?" Never switch the main workspace without this question — it is shared: it may be dirty, or hosting another feature's review. On approval:

1. Commit the work on the feature branch in the worktree.
2. Remove the worktree, then check out the feature branch in the main workspace.
3. Run the deferred runtime verification there (verify skill / browser).
4. Hand over to the user for manual review and wait.

Every finding from the user's review is fixed mechanically — no triage, no deferral, no scope pushback. Fixes re-enter phase 6 → 7a → 7b, now all in the main workspace (no re-ask — the branch already occupies it). Only explicit user approval exits the loop and opens phase 8.

### 8. Finish
**REQUIRED SUB-SKILL:** superpowers:finishing-a-development-branch
You may commit on feature branches only. Commits on `staging`/`master`, rebases into them, and all pushes are user-only. PRs target `staging`.

## Parallel Features

One session may run several /implement cycles at once — one worktree per feature.

- Interactive phases (brainstorm, grill, plan review, manual CR) are serial with the user; execute and quality-gate phases may run concurrently via subagents.
- Every subagent prompt must pin the feature's worktree path (`.worktrees/<branch>`) so no work leaks into the main workspace or a sibling worktree.
- Only one feature at a time occupies the main workspace for phase 7b; a feature waiting on the user parks while other cycles continue.

## Red Flags — STOP, return to the skipped phase

- "This change is too small for the full cycle"
- "Brainstorming already covered everything, grilling adds nothing"
- "The user is experienced, they don't need to be grilled"
- "I'll grill after I sketch the code"
- "Tests after implementation achieve the same thing"
- "The suite passed locally last time, no need to re-run"
- "Main workspace looks clean, I can switch it without asking"
- "7a found nothing, the manual review is a formality"

| Excuse | Reality |
|--------|---------|
| "Requirements are already clear" | Grilling resolves branches you haven't seen yet. Invoke it. |
| "User is in a hurry" | Only an explicit waiver from the user skips phase 2. Urgency is not a waiver. |
| "One-line fix" | One-line fixes still get TDD, the quality gate, and review. |
| "Review can wait until PR" | Phase 7 happens before finishing, every time. |
| "The worktree can live in /tmp" | The container mounts only the repo root. Outside `.worktrees/` the quality gate cannot run. |
| "I can verify runtime by serving the worktree myself" | Runtime verification waits for phase 7b in the main workspace. No serving hacks. |
| "7a was clean, manual review is a formality" | Phase 7b exits only on explicit user approval. Ask, then wait. |
| "I'll rebase it into staging myself" | Feature-branch commits only. staging/master and all pushes are user-only. |
