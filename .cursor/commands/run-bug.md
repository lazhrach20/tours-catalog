# Command: Run Orchestrator for a Bug

## When to use

Use this command when you want to fix a **single bug** described in `prds/bugs/`.

It uses the **Bug Orchestrator** skill, which will:
- normalise repro steps
- scan the codebase for likely root cause areas
- assess whether the bug is likely already fixed (baseline)
- produce a fix plan
- decompose into atomic tasks
- delegate implementation to subagents (**autonomously** — no per-batch `yes`)
- run a review loop and produce a report

You type `yes` **only between phases**, not during implementation.

---

## How to start

Open a **new chat** in Cursor, then type one of:

```
/run-bug prds/bugs/<your-bug>.md
```

```
orchestrate bug prds/bugs/<your-bug>.md
```

```
run bug orchestrator for prds/bugs/<your-bug>.md
```

---

## Execution artifacts

State + report are written under:

```
.execution/bugs/<timestamp>/
  context.json
  report.md
```

