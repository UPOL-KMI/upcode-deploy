# UPcode — verified source revisions

**What this file is.** `repos.lock` records *which* revisions the stack is pinned to. This file
records *what was verified about them*, and what is known not to work. A commit hash cannot tell
you whether anybody ever ran it.

Bump both together: change `repos.lock`, re-verify, and add a row here saying what you ran.

---

## Verified set — 2026-09-17

| Component  | Source                   | Commit     | Dated      |
| ---------- | ------------------------ | ---------- | ---------- |
| `api`      | `UPOL-KMI/upcode-api`    | `10bf26d4` | 2026-09-17 |
| `worker`   | `UPOL-KMI/upcode-worker` | `cf26d8c`  | 2026-09-17 |
| `isolate`  | `UPOL-KMI/upcode-isolate`| `bfdcf98`  | 2026-09-17 |
| `monitor`  | `UPOL-KMI/upcode-monitor`| `e6f8a1d`  | 2026-02-13 |
| `broker`   | `UPOL-KMI/upcode-broker` | `abdc95c`  | 2022-12-04 |
| `cleaner`  | `UPOL-KMI/upcode-cleaner`| `0a5e390`  | 2025-07-16 |
| `web-next` | `UPOL-KMI/upcode-web-ui` | `2330b1e`  | 2026-09-17 |

**`worker` and `isolate` were pinned to the wrong commits, and only a real build found it.** Both
lines read "base of `upcode`" -- the commit *before* our patches -- while this machine had the
branch tips checked out from before the lock was written, so every build here used code the lock
did not name. On a clean server the pinned `isolate` is upstream 1.8.1, whose Makefile has no
`isolate-cg-keeper` target, and the worker image fails to build at that line. The 2.7 upgrade and
the worker change that goes with it (`--cg-timing`, which Isolate 2.0 removed) are on the `upcode`
tips, which is where both pins now point.

The rehearsal below had not caught it because it stopped at `docker compose config`: the pins were
cloned, the configuration parsed, and nothing compiled. It builds now.

**A clean clone was rehearsed rather than assumed.** `pull-repos.sh` was run into an empty
directory with `ssh -o BatchMode=yes`, which fails rather than prompts: all seven repositories
cloned over HTTPS with no key present. `docker compose config` then parsed from nothing but
`.env.example` copied to `.env` -- which is what catches a variable removed from the template while
something still refers to it -- and reports eight services and exactly one published port, 80.
Every build context and Dockerfile the configuration names exists in that tree, and
`docker compose build` then produced all six images from those revisions -- worker and sandbox
compiled from source included.

The script defaulted to SSH for the three forks until this round, and that is how the operator's
first attempt on a real server failed: three mirrors cloned, then `Permission denied (publickey)`.
Everything here is public, so HTTPS is the default now and `USE_SSH=1` is the opt-in for whoever
pushes.

`api` is pinned to `upcode-email-design`, which is the branch the e-mail round is on. Once its pull
request into `upcode` is merged the commit is unchanged; only the branch that contains it moves, so
the pin needs no bump — but the comment in `repos.lock` does.

**`web-app` is gone from this table because it is gone from the deployment.** It was a second
complete frontend talking to the same API with the same rights, on a published port nobody watched,
and its one remaining use — a side-by-side reference during the cutover — ended when the cutover
did. The service, its build directory, its `.env` entry, its line in `repos.lock` and its clone in
`pull-repos.sh` were all removed together; `docker compose config` lists eight services now.

**What was verified in this round**, against the running stack rather than by reading:

- **Every message the system sends** was rendered through core-api's own Latte engine with the
  deployment's real configuration (`tools/email-preview.sh`, 54 templates) and read. The header
  carries the application's own lockup, generated from the frontend's brand component by
  `tools/brand-email-logo.mjs`.
- **The logo in those messages reaches a reader.** It is served at `%api.address%/emails/img/...`,
  which this deployment's proxy did not route: `/api/` is deliberately narrowed to `/api/v1` so the
  frontend keeps its own API routes, and `/api/emails/...` fell through to the frontend as a 404 —
  a broken image in every message. Measured before and after: `404 text/html`, then
  `200 image/png`. `/api/v1/instances`, `/api/auth/login` and `/api/config` were re-checked after
  the change; only the intended path moved.
- **The image was missing from the image.** The PNG existed in the running `api` container and not
  in what built it: it had been copied in by hand in an earlier session, and every rebuild lost it.
  The `api` image is rebuilt from the pinned commit here, which is what makes the logo survive a
  deployment rather than a session.
- **Backup and restore** (`backup.sh`, `restore.sh`): a backup was taken from the running stack —
  86 tables, 94 files — and its retention was checked to remove only what it names. The restore
  script has been read but **not run against live data**; that remains to be done deliberately.
- **Docker's own log capture is capped** at 3 × 10 MiB per service. This does not touch the `*_log`
  volumes, which hold the applications' own files: `api_log` was 57 MB, 53 MB of it core-api's
  `user_actions.log`, which has no rotation of its own and no switch in configuration.

## Verified set — 2026-09-11

| Component  | Source                       | Commit     | Dated      |
| ---------- | ---------------------------- | ---------- | ---------- |
| `api`      | `UPOL-KMI/upcode-api`        | `7471b71f` | 2026-07-23 |
| `worker`   | `UPOL-KMI/upcode-worker`     | `f267aa9`  | 2025-10-25 |
| `isolate`  | `UPOL-KMI/upcode-isolate`    | `25d3f48`  | 2025-07-14 |
| `monitor`  | `UPOL-KMI/upcode-monitor`    | `e6f8a1d`  | 2026-02-13 |
| `broker`   | `UPOL-KMI/upcode-broker`     | `abdc95c`  | 2022-12-04 |
| `cleaner`  | `UPOL-KMI/upcode-cleaner`    | `0a5e390`  | 2025-07-16 |
| `web-app`  | `ReCodEx/web-app` (upstream) | `fc6fdaf`  | 2026-08-01 |
| `web-next` | `UPOL-KMI/upcode-web-ui`     | `fcb7d9a`  | 2026-09-11 |

**How it was verified.** The database and file storage were wiped and rebuilt from the api
entrypoint's fresh-database path, then seeded (`pnpm seed`). Against that instance the new
frontend's full suite was run **twice: 311 end-to-end tests pass and 3 skip, 0 failures**, with
`retries` at 0, plus 287 unit tests and five static checks (`typecheck`, `lint`, `format:check`,
`build`, `test`). After both runs the seeded fixtures were **unchanged** — four solutions on the
primary assignment, one on the second-deadline one, none on the deliberately-empty one, one
instance, six groups, 26 exercises, an empty failure queue — which is the part that says the suite
does not quietly consume its own fixtures.

The three skips are the one fixture this deployment can no longer produce: a submission failure,
which used to be a side effect of a sandbox that could not run. `web-next`'s PF-017 establishes
that none can be made from an API a spec can call; each skip says so rather than passing quietly.

**What this run also proves is that the seed is fixed rather than the instance patched.** Against
an empty database, `pnpm seed` produces fixtures that grade: the correct solutions 10/10, the wrong
one 0/10, the reference solution 1.0, with nothing repaired by hand.

**Evaluation is verified end to end as of 2026-09-11** — a correct seeded solution scores full
points and a wrong one zero, through the real submit path. See "The sandbox works" below for what
it took and what was verified; the paragraph that used to stand here said submitted code does not
run on this host, and that has not been true since plan 002.

**This is a source pin, not an image pin,** and on 2026-09-11 the two disagreed: the running
`recodex-api` and `recodex-worker` images predated the C#/Java toolchain change already committed
here (see "Language runtimes" below). The revisions above describe `repos/`; what is actually
serving traffic depends on when `docker compose build` last ran. Worth checking with
`docker image inspect recodex-api recodex-worker --format '{{.Created}}'` before believing a
verdict about the stack.

**`api` is pinned to the base of its `upcode` branch, not the tip.** The tip carries a README commit
that was not part of this build. Code-identical; this file is about what ran.

---

## What is forked, and what is not

Our forks live in the [`UPOL-KMI`](https://github.com/UPOL-KMI) organisation as
`upcode-<component>`. In each one:

| Branch   | What it is                                                                |
| -------- | ------------------------------------------------------------------------- |
| `master` | Untouched mirror of upstream. Nothing of ours is committed there.         |
| `upcode` | Our integration branch, and the default, on the three we change.          |

`api`, `worker` and `isolate` have an `upcode` branch and a fork notice in the README.
`monitor`, `broker` and `cleaner` are unmodified mirrors — GitHub's own "forked from" banner and the
untouched `LICENSE` are the whole of what an unmodified fork needs, and adding a notice would make a
clean mirror unclean for no benefit.

**`web-app` was never forked, and is now not fetched either.** It was the legacy frontend, pinned
by commit straight to upstream because nothing of ours was ever going to change in it. It was
removed from the deployment on 2026-09-17 — service, build, `.env` entry, pin and clone — so
`pull-repos.sh` no longer carries a source exception at all: `api`, `worker` and `isolate` come
from the fork over SSH and everything else over HTTPS.

**Licences differ, and `isolate` is the odd one.** Everything above is MIT (© 2016 ReCodEx Team)
except `isolate`, which is **GPL-2.0-or-later** (© Martin Mareš, Bernard Blackham; upstream
`ioi/isolate`). Running a service on it is not distribution and triggers no obligation; handing the
binary or a container image to anybody outside the university does, and then our changes have to be
offered as source. Keeping the fork public satisfies that by itself.

---

## Patched at build time, and belongs upstream instead

`services/api/patch-compatibility.php` rewrites three things in `repos/api` during the image build.
All three are faults against *current* upstream, not local misconfiguration, so all three are
candidates to send to ReCodEx — and until then they belong as commits on `upcode-api`'s `upcode`
branch rather than as a patch script:

1. ~104 pre-2022 migrations call `getDatabasePlatform()->getName()`, which `doctrine/dbal` 4
   (pinned by upstream's own `composer.lock`) removed. Rewritten to an
   `instanceof AbstractMySQLPlatform` check.
2. Several of those migrations mix DDL with an explicit `beginTransaction()`/`commit()` pair.
   MySQL/MariaDB commits implicitly on DDL, which desynchronises Doctrine's savepoint bookkeeping
   and crashes with `SAVEPOINT ... does not exist`. Fixed by `isTransactional(): false`.
3. `RuntimeImport` declares its own `--silent`, which now collides with one `symfony/console`
   registers globally — `LogicException` before the command runs.

Beyond those, a **fresh** database is built with `orm:schema-tool:create` from the current entity
mappings and the migration history is marked as applied, because a clean replay of eight years of
migrations hits `ALTER TABLE ... CHANGE` on foreign-key columns that current MariaDB refuses even
with `FOREIGN_KEY_CHECKS=0`. An **existing** database still runs `migrations:migrate` normally.

---

## Known not working, with the path out

### The sandbox works, and so does evaluation

**Fixed 2026-09-11: the sandbox runs.** Isolate is **2.7** now (plan 001), the worker delegates
itself a cgroup v2 subtree at start-up, and all of this is verified on Docker Desktop for macOS:

```
== cgroup v2 subtree ready at /sys/fs/cgroup/isolate (controllers: cpuset cpu memory) ==
Checking for cgroup support for memory.max ... PASS       (was CAUTION / "cannot be used")
Using cgroup root: /sys/fs/cgroup/isolate
```

Python runs inside it and the limits are real, checked one at a time by hand:

| Case | Result |
| ---- | ------ |
| read stdin, print a sum | `exitcode:0`, correct output, meta carries all seven keys the worker parses |
| infinite loop, `--wall-time=2` | `status:TO`, "Time limit exceeded (wall clock)", killed at 2.002 s |
| allocate 400 MB, `--cg-mem=64MB` | `cg-mem:65536`, `cg-oom-killed:1`, `exitsig:9`, `status:SG` |
| `sys.exit(3)` | `status:RE`, "Exited with error status 3" |

Memory limits *are* enforced despite `isolate-check-environment`'s swap CAUTION — it now reads
"although accounted for", where before it was a FAIL saying accounting was absent.

**Fixed 2026-09-11, later the same day: evaluation is verified end to end.** What the cgroup
failure had been hiding was a separate, older set of bugs in how the *exercise* was configured —
three of them, all in `repos/web-next`, none in core-api, and none specific to macOS or cgroups.
The compiled job read `python3 <runner> ${EVAL_DIR}/` with no file to run, and copied the solution
to a file named literally `*.py`, because the environment's `source-files` wildcard was written as
a one-element array (core-api only expands a scalar) and the test was missing the pass-through
compilation pipeline that binds the submitted files at all. Fixing those made the entry point a
*submit-time* variable, which the new frontend had never sent. Full account in
**`docs/plans/002`**; the work itself is `web-next`'s **PF-016**.

**Verified through the real submit path**, not by reading configuration:

| Seeded solution | Result |
| ---- | ------ |
| `[seed] correct` | **10/10**, `Test 1` OK |
| `[seed] wrong` | **0/10**, `Test 1` FAILED |
| `[seed] multi-file` (`main.py` + `greeting.py`) | **10/10**, `Test 1` OK |

and the compiled job now reads `cp ${SOURCE_DIR}/solution.py …` and
`python3 <runner> ${EVAL_DIR}/solution.py`.

**One consequence worth knowing before it surprises somebody:** a solution submitted as a single
ZIP archive cannot be graded by an exercise whose `source-files` is `*.py`. core-api matches the
wildcard against the *uploaded* file name (`solution.zip`), not the entries inside it, so such a
submission is refused. That is upstream behaviour, not something this deployment introduced.

**All six runtime environments grade, verified one at a time on 2026-09-12.** One exercise per
environment, configured the way the app writes one, a reference solution submitted, the verdict
read back — `bash`, `c-gcc-linux`, `cxx-gcc-linux`, `python3`, `cs-dotnet-core` and `java` each
score 1.0 with `Test 1` OK. The probes were deleted afterwards and the seeded fixtures are
untouched.

**Two of the six needed a fix each, and both were the same shape: a toolchain the sandbox could not
reach at the path its runtime package names.**

**C and C++ had never compiled here.** `GCC Compilation` and `G++ Compilation` hardcode
`compiler-exec-path: "/usr/local/recodex-gcc/bin/gcc"` — ReCodEx's own production images build a
GCC there — while this image installs Debian's into `/usr/bin`. Every C submission would have died
with `execve("/usr/local/recodex-gcc/bin/gcc"): No such file or directory`, which reads like a
missing toolchain and is a toolchain in the wrong place. `services/worker/Dockerfile` links the
expected path at the real one. **Linked rather than corrected in the pipeline**, because pipelines
come from the imported runtime package and an edit there is overwritten by the next
`runtimes:import`; providing the path the package asks for is the deployment's job.

**C# grades too, as of 2026-09-11** — and getting there found a second bug of the same family as
plan 001's PATH one. `/usr/bin/dotnet` is a symlink to `/opt/dotnet/dotnet`, and isolate binds
`/usr`, `/bin` and `/lib` into the sandbox and nothing else, so inside it that symlink pointed
nowhere: every C# submission died with `execve("/usr/bin/dotnet"): No such file or directory`,
which reads like a missing toolchain and was a missing **mount**. `services/worker/config.yml`
binds `/opt` now, read-only. Verified end to end: a reference solution compiled by Roslyn
(`Visual C# Compiler version 4.11.0`) scores 1.0 with `Test 1` OK, and Python still does too.

**Java had the same class of fault wearing Debian's clothes:** the JDK's `conf/` is symlinks into
`/etc/java-17-openjdk`, which the sandbox could not see, so `javac` started and died with
`java.lang.InternalError: Error loading java.security file`. That directory is bound now, and a
Java reference solution compiles with `javac` and scores 1.0 — verified on 2026-09-12, where the
line here used to say only that the toolchain was reachable.

**One real bug was found and fixed on the way**, also previously masked: the worker gave sandboxes
`PATH=/usr/bin:/bin`, and this image builds Python 3.13 from source into `/usr/local` (Debian 12
ships 3.11), so `/usr/bin/python3` does not exist and every Python submission would have died with
"Exited with error status 127". `services/worker/config.yml.template` now puts `/usr/local/bin`
first.

### Mail: not configured

`SMTP_HOST` is still `smtp.example.com` and `SMTP_USER`/`SMTP_PASSWORD` are empty. Password reset,
email verification, invitations by mail and every notification therefore go nowhere. The frontend
builds and tests the request side against core-api regardless; nothing arrives.

### Language runtimes: all six are in the running stack, and all six grade

**The deployment code has six**: `bash`, `c-gcc-linux`, `cxx-gcc-linux`, `python3`,
`cs-dotnet-core` and `java`, added on 2026-08-21 and verified then in rebuilt images — dotnet
8.0.424, javac 17.0.20, both runtime packages imported, the API advertising all six.

**All six are in the running stack as of 2026-09-11**, after `docker compose build api worker` and
importing the two packages by hand. What follows is the state before that, kept because the trap it
describes is the one that recurs. **The api image was the one behind**. Re-checked
2026-09-11 after plan 001: `recodex-worker` was rebuilt that morning and **does carry the
toolchains** — `dotnet 8.0.425` and `javac 17.0.20.1`, confirmed inside the running container. It is
`recodex-api`, built 2026-08-17, that predates the change, so the `cs-dotnet-core` and `java`
runtime packages its Dockerfile fetches are not in the image and `/v1/runtime-environments` answers
`bash`, `c-gcc-linux`, `cxx-gcc-linux`, `python3`.

An earlier version of this paragraph said both images were old and named the worker's build date as
2026-07-30; plan 001's rebuild had already overtaken it. Nothing is wrong with the code — and the
rebuild this needs is `docker compose build api`, not both.

**And rebuilding is necessary but not sufficient** — this is the part that will waste an afternoon
if it is not written down. `services/api/docker-entrypoint.sh` gates both `db:fill init` and the
`runtimes:import` loop on `[ ! -f storage/.seeded ]`, and `storage/` is the `api_storage` volume.
That marker was created by the wipe on 2026-09-11, so on the next boot the import is **skipped** and
a freshly built api image will still not register `cs-dotnet-core` with core-api. Two ways round it:

**And a third thing, found by doing it**: `runtimes:import` run through `docker compose exec`
runs as **root**, because this container has no `USER` directive, so every file it writes into
`storage/` is root-owned and `0600`. php-fpm serves as `www-data` and cannot read them, so the
worker's very next job fails with `Cannot fetch files ... (500) HTTP response code said error` and
the blob is sitting right there on disk. The entrypoint chowns after its own console commands; a
hand-run import has to do the same.

```bash
# Rebuild, then import the new packages by hand -- keeps the database.
# `worker` only if its toolchains are older than the change; check before assuming.
docker compose build api && docker compose up -d
docker compose exec api php bin/console runtimes:import --yes     /opt/recodex-runtimes/cs-dotnet-core-2024-12-15.zip
docker compose exec api php bin/console runtimes:import --yes     /opt/recodex-runtimes/java-2024-12-15.zip
# ...and hand the files back to the user that serves them.
docker compose exec api chown -R www-data:www-data /opt/recodex-core/storage
```

or wipe `mysql_data` + `api_storage` again and let first boot do it, which also costs the seeded
fixtures. The first is what you want unless you were going to wipe anyway.

**The .NET version is not free to choose.** `cs-dotnet-core`'s own `Program.runtimeconfig.json`
targets `netcoreapp8.0` with no `rollForward` key, so a worker carrying .NET 9 or 10 looks
correctly provisioned and fails every C# submission at run time. The package *description* still
claims v6; the config shipped beside it says 8, and the config is what gets loaded. Hence the pin
in `services/worker/Dockerfile`, and see README's "Language toolchains" for the full reasoning.

### The new frontend serves `/` — done 2026-09-11, plan 004

`services/proxy/nginx.conf.template`'s `location /` points at `web-next:3000`. The legacy app is
still built and still running, now on a published host port (`WEB_APP_PORT`, default 8080), because
it is a useful reference while the pipelines and runtimes are still being worked on.

**"Switching it over is one entry in that template" — which this file used to say — was wrong, and
`repos/web-next/docs/ROUTES.md` had said so all along:** every legacy URL needed somewhere to go
first. `/app/...` paths are in bookmarks, in old emails and on teachers' slides, and every route in
the new app moved — a locale prefix on all of them, six group screens collapsed into `?tab=`, and
solutions dropping their assignment context. That mapping is now `redirects()` in
`repos/web-next/next.config.ts`, checked by driving 24 legacy paths against a real build rather
than by reading the table.

**Two smaller things the cutover turned up.** The proxy's `depends_on` had to gain `web-next`:
nginx resolves every upstream name at start-up and refuses to start if one does not resolve, so a
missing dependency there is not a slow first request but a proxy that never comes up. And `web-app`
had **no published port at all** — this proxy was the only way anybody reached it — so the switch
would have made it unreachable rather than merely no longer the front door.

**Retiring `web-app`** — the service, its build, and `repos.lock`'s exception for the one unforked
repo — was still to come when this was written. It was done on 2026-09-17; see the verified set at
the top.
