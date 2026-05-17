# Command: Run Orchestrator for a Task

## When to use

Use this command when you want to implement a **single feature task** described as a mini‑PRD in `prds/tasks/`.

It uses the **Task Orchestrator** skill, which will analyse the task PRD, scan the codebase, produce a plan, decompose it into atomic tasks, delegate to subagents, run a review loop, and produce a report — without writing code itself.

---

## How to start

Open a **new chat** in Cursor (fresh context recommended), then type one of:

```
/run-task prds/tasks/<your-task>.md
```

```
orchestrate task prds/tasks/<your-task>.md
```

```
run task orchestrator for prds/tasks/<your-task>.md
```

---

## What it will do

It asks for **`yes` only between phases**. Inside **Implementation**, all tasks run automatically without batch prompts.

It includes a dedicated **baseline** phase that reports what parts of the task are already implemented (DB/backend/frontend/infra) before it decomposes the remaining work into atomic tasks.

State + report are written under:

```
.execution/tasks/<timestamp>/
  context.json
  report.md
```

