# Command: Run Orchestrator for a PRD

## When to use

Use this command when you want to implement a project PRD end-to-end using the Orchestrator skill. The Orchestrator will analyse the PRD, scan the codebase, produce a plan, decompose it into atomic tasks, delegate them to subagents, run a review loop, and produce a final report — all without writing code itself.

---

## How to start

Open a **new chat** in Cursor (important: fresh context = fewer tokens consumed), then type one of the following:

```
/run-prd prds/tz_main.md
```

```
orchestrate prds/tz_main.md
```

```
run orchestrator for prds/tz_main.md
```

Replace the path with the PRD you want to execute.

---

## User approval (how often you type `yes`)

| Moment | Your action |
|--------|-------------|
| After each **phase** completes (Analysis → Codebase → Planning → Decomposition → Implementation → Review → Report) | Type `yes` to start the **next** phase |
| **Inside Implementation** (Phase 5) | **Nothing** — all tasks run automatically |
| **Inside Review** (Phase 6) | **Nothing** — all domain checks run automatically |
| Progress messages (`5 / 12 tasks`) | Informational only — no reply needed |

Commands:
- `yes` — run the next phase (or finish the current phase on resume)
- `no` / `abort` — pause; state saved in `.execution/`, resume later with the same command

Between Phase 4 and Phase 5 you may edit the task list in your `yes` reply.

---

## Execution phases (in order)

| # | Phase | Model | What happens |
|---|-------|-------|--------------|
| 1 | PRD Analysis | fast | Extracts features, constraints, acceptance criteria |
| 2 | Codebase Scan | fast | Maps existing files, modules, services |
| 3 | Planning | default | High-level architecture and layer ordering |
|  4 | Decomposition | default | Atomic task list with model assignment |
| 5 | Implementation | fast / default | **All tasks** executed without further prompts |
| 6 | Review | fast (readonly) | Quality check per domain, up to 3 retries each |
| 7 | Report | fast | Summary written to `.execution/<id>/report.md` |

---

## Resuming a paused execution

If the Orchestrator was interrupted, start a new chat and use the same command:

```
/run-prd prds/tz_main.md
```

The Orchestrator will detect the existing `.execution/<timestamp>/context.json`, ask **once** to continue, then run the rest of the current phase autonomously.

---

## Execution state files

All state lives under `.execution/`:

```
.execution/
  2026-04-11-14-30/
    context.json    ← live execution state (read by orchestrator on resume)
    report.md       ← written at the end of Phase 7
```

You can inspect `context.json` at any time to see which tasks are completed, failed, or pending.

---

## Tips

- If a task is marked `failed`, the orchestrator continues with the next task and lists failures in the final report.
- The `signal: 42` field in `context.json` confirms the file was created by the Orchestrator and not edited manually.
