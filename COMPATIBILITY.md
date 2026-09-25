# UPcode — verified source revisions

**What this file is.** `repos.lock` records *which* revisions the stack is pinned to. This file
records *what was verified about them*, and what is known not to work. A commit hash cannot tell
you whether anybody ever ran it.

Bump both together: change `repos.lock`, re-verify, and add a row here saying what you ran.

---

## Verified set — 2026-09-25 (an optional file name is optional again)

| Component  | Source                    | Commit     | Dated      |
| ---------- | ------------------------- | ---------- | ---------- |
| `api`      | `UPOL-KMI/upcode-api`     | `383b908`  | 2026-09-25 |
| `worker`   | `UPOL-KMI/upcode-worker`  | `cf26d8c`  | 2026-09-17 |
| `isolate`  | `UPOL-KMI/upcode-isolate` | `bfdcf98`  | 2026-09-17 |
| `monitor`  | `UPOL-KMI/upcode-monitor` | `e6f8a1d`  | 2026-02-13 |
| `broker`   | `UPOL-KMI/upcode-broker`  | `abdc95c`  | 2022-12-04 |
| `cleaner`  | `UPOL-KMI/upcode-cleaner` | `0a5e390`  | 2025-07-16 |
| `web-next` | `UPOL-KMI/upcode-web-ui`  | `9f02c0b`  | 2026-09-25 |

**`api` is unchanged**; only the `web-next` image needs rebuilding.

**This closes a gap the previous set opened.** The second box of an extra-file pair is labelled
"(optional) new file name" and was written through as the empty string, which is not a name: the
worker builds the destination as `<directory>/<name>`, so an empty one resolves to the directory
itself and the whole job dies with `Cannot open file /var/recodex-worker-wd/.../01-dot-product/ for
writing` — a message naming neither the field nor the row that caused it. Nothing upstream objected;
the form's schema is two bare strings and core-api accepts the empty entry.

An empty name now means the file keeps its own, which is what the label always promised. A pair
whose file is "— none —" is dropped rather than written empty, which had the same failure mode one
step further along. Both rules sit where the pairs are serialised, so input files get them from the
same place as extra files.

**Why it matters more than it did when it was written.** The set before this one changed the
entry-point dropdown to offer what a test's extra files *deliver*, reading an empty name as "the
file keeps its own" — while the save still wrote the empty string. The dropdown therefore invited
an author to rely on a fallback the save did not honour. These two agree again.

**Measured through the interface, not the API.** A test's "new file name" box was cleared and the
form saved; the stored configuration came back as `extra-file-names: ['main.py']` rather than
`['']`. All six reference solutions on that exercise were then re-run: six evaluations, no
failures, the correct one scoring 1.0. Earlier the same empty value was reproduced on this
deployment and produced the worker error quoted above.

---

## Verified set — 2026-09-25 (the entry point offers what will be there)

| Component  | Source                    | Commit     | Dated      |
| ---------- | ------------------------- | ---------- | ---------- |
| `api`      | `UPOL-KMI/upcode-api`     | `383b908`  | 2026-09-25 |
| `worker`   | `UPOL-KMI/upcode-worker`  | `cf26d8c`  | 2026-09-17 |
| `isolate`  | `UPOL-KMI/upcode-isolate` | `bfdcf98`  | 2026-09-17 |
| `monitor`  | `UPOL-KMI/upcode-monitor` | `e6f8a1d`  | 2026-02-13 |
| `broker`   | `UPOL-KMI/upcode-broker`  | `abdc95c`  | 2022-12-04 |
| `cleaner`  | `UPOL-KMI/upcode-cleaner` | `0a5e390`  | 2025-07-16 |
| `web-next` | `UPOL-KMI/upcode-web-ui`  | `4a12945`  | 2026-09-25 |

**`api` is unchanged**; the round is one frontend module and its tests, closing issue 11 on
`upcode-web-ui`. Only the `web-next` image needs rebuilding.

**What it fixes is an afternoon the operator already lost.** A test's *entry point* writes a name
into the run command; *extra files* is the only thing that copies a file into the sandbox. The
dropdown listed the exercise's own attachments, which look available and are not — attaching a file
to an exercise puts it nowhere near the box. Picking one and leaving extra files empty gave eleven
tests failing with `FileNotFoundError: '/box/main.py'`, while the job configuration named the file
once per test as the run argument and in no `fetch` or `cp` task at all.

The dropdown now offers **only what that test's extra files deliver**, so it is empty until one is
added and the field's description says where its options come from. A renamed delivery is offered
under the name it lands as. A value already configured stays selectable even when it is not in the
list — an entry point naming a *student's* submitted file is the one case the screen cannot express
and must not silently drop — and that is what the warning beneath the field is for. It warns rather
than refusing, because core-api accepts such a value, and rather than auto-adding, because
supplying a student's own file would be wrong.

**Measured in the browser against a real eleven-test exercise**, not reasoned about: eleven warnings
with extra files empty and none with them set; clearing one test's pair with the form open leaves
that test offering only its stale value, switches its description to the "add extra files" line and
raises exactly one more warning, all without a reload; and a test delivering `main.py` as `run.py`
offers `run.py`, warns while the entry point still says `main.py`, and falls quiet once `run.py` is
chosen. The experimental exercise used for this was put back to its working configuration
afterwards and re-checked as `isBroken: false`.

**Still not run: the end-to-end suite**, which this deployment has no `[seed]` fixtures for. The
rule is covered by fourteen unit tests instead.

---

## Verified set — 2026-09-25 (the dashboard shows your own courses)

| Component  | Source                    | Commit     | Dated      |
| ---------- | ------------------------- | ---------- | ---------- |
| `api`      | `UPOL-KMI/upcode-api`     | `383b908`  | 2026-09-25 |
| `worker`   | `UPOL-KMI/upcode-worker`  | `cf26d8c`  | 2026-09-17 |
| `isolate`  | `UPOL-KMI/upcode-isolate` | `bfdcf98`  | 2026-09-17 |
| `monitor`  | `UPOL-KMI/upcode-monitor` | `e6f8a1d`  | 2026-02-13 |
| `broker`   | `UPOL-KMI/upcode-broker`  | `abdc95c`  | 2022-12-04 |
| `cleaner`  | `UPOL-KMI/upcode-cleaner` | `0a5e390`  | 2025-07-16 |
| `web-next` | `UPOL-KMI/upcode-web-ui`  | `4e1112d`  | 2026-09-25 |

**`api` is unchanged**; the round is one frontend module and its tests, closing issue 9 on
`upcode-web-ui`. The dashboard's calendar and "Moje výuka" read the inherited-plus-direct set of
groups, so an administrator of a department opened the app onto every colleague's deadlines; they
now read the direct set, as the sidebar has since the round of 2026-09-20. The review queues, the
groups a reader studies in, and the name lookup those queues print through all stay wide — core-api
decides whose plate a review is on, and narrowing that would hide assigned work.

**Verified by measurement, and the measurement was negative.** The issue named `ALGO1 - Úterý` — a
course under a parent the operator administers, belonging to another teacher — as what should
disappear. It holds **zero assignments**, so it contributes no deadlines and the dashboard renders
identically before and after: the change was stashed, the page re-read, and the two outputs compared.
The behaviour is therefore pinned by unit tests rather than by the page, and the ticket records that
the e2e seed has no inherited-but-not-taught group to assert against. Five static checks pass
(`typecheck`, `lint`, `format:check`, `build`, `test`: 407 unit tests).

> **`pull-repos.sh` rewrites each `origin` to HTTPS**, which is what lets a keyless server fetch —
> and what makes `git push` fail on a machine that has keys, with `could not read Username for
> 'https://github.com'`. Push to the SSH URL explicitly (`git push git@github.com:UPOL-KMI/<repo>.git
> <branch>`), or set the remote back after pulling. It is not a broken checkout.

---

## Verified set — 2026-09-25 (the catalogue says which group, and lets you search for it)

| Component  | Source                    | Commit     | Dated      |
| ---------- | ------------------------- | ---------- | ---------- |
| `api`      | `UPOL-KMI/upcode-api`     | `383b908`  | 2026-09-25 |
| `worker`   | `UPOL-KMI/upcode-worker`  | `cf26d8c`  | 2026-09-17 |
| `isolate`  | `UPOL-KMI/upcode-isolate` | `bfdcf98`  | 2026-09-17 |
| `monitor`  | `UPOL-KMI/upcode-monitor` | `e6f8a1d`  | 2026-02-13 |
| `broker`   | `UPOL-KMI/upcode-broker`  | `abdc95c`  | 2022-12-04 |
| `cleaner`  | `UPOL-KMI/upcode-cleaner` | `0a5e390`  | 2025-07-16 |
| `web-next` | `UPOL-KMI/upcode-web-ui`  | `a553384`  | 2026-09-25 |

**`api` is unchanged from the set above**; the whole round is in the frontend, so only that image
needs rebuilding. Two rounds of frontend work land together, both of them issue 8 on
`upcode-web-ui`: the Group column and the group filter (X-016), and then the filter made searchable
(X-022). **No core-api change in either** -- `groupsIds` was already on every `/v1/exercises` row and
`filters[groupsIds][]` already expands through the ancestral closure; only the frontend's own type
failed to declare the field.

**What the filter does now.** A query is matched against a group's whole path, so searching a course
code returns the course *and* every group beneath it, and the matches are drawn as a tree with their
containers above them. That is the comparison a teacher is actually making, because filtering by the
course returns what any of its seminar groups would return and more -- and the flat `<select>` the
first round shipped withheld exactly that.

**Measured on this deployment, in the browser.** Typing `kmi/jp` leaves the course and its seminar
group on screen under `Univerzita Palackého v Olomouci / Katedra Informatiky / Výuka`; Enter takes
the first match rather than clearing the filter; submitting produces
`?group=b84abd8e-…` and the narrowed list, with the sentence that explains why the Group column may
name a different group than the filter did. **The screen still works without JavaScript**: the
server-rendered HTML was fetched and checked to contain the original `<select name="group">`, which
the combobox replaces only once scripting runs. Five static checks pass (`typecheck`, `lint`,
`format:check`, `build`, `test`: 402 unit tests). **Not run:** the e2e suite, which still cannot run
on an instance without `[seed]` fixtures.

> **A rebuild from the working tree is not a deployment.** This round was first made visible by
> `docker compose build web-next` against the checkout, which serves whatever is checked out and
> leaves `repos.lock` pointing somewhere else entirely -- so the operator's own machine showed the
> new work while a fresh clone would still have built the old. Fine for showing somebody something;
> never the end of a round. The pin above is what makes it real.

---

## Verified set — 2026-09-25 (a declined notification stays declined)

| Component  | Source                    | Commit     | Dated      |
| ---------- | ------------------------- | ---------- | ---------- |
| `api`      | `UPOL-KMI/upcode-api`     | `383b908`  | 2026-09-25 |
| `worker`   | `UPOL-KMI/upcode-worker`  | `cf26d8c`  | 2026-09-17 |
| `isolate`  | `UPOL-KMI/upcode-isolate` | `bfdcf98`  | 2026-09-17 |
| `monitor`  | `UPOL-KMI/upcode-monitor` | `e6f8a1d`  | 2026-02-13 |
| `broker`   | `UPOL-KMI/upcode-broker`  | `abdc95c`  | 2022-12-04 |
| `cleaner`  | `UPOL-KMI/upcode-cleaner` | `0a5e390`  | 2025-07-16 |
| `web-next` | `UPOL-KMI/upcode-web-ui`  | `cf20600`  | 2026-09-20 |

**`web-next` is unchanged from the set above**; the whole round is two lines in `api`, so only that
image needs rebuilding.

**Publishing a shadow assignment with the notification unticked wrote to every student anyway.**
`ShadowAssignmentsPresenter::actionUpdateDetail()` recognised the optional `sendNotification` by
truthiness rather than by presence. The frontend sends a JSON body and always sends the field, so a
declined notification arrives as boolean `false`, the truthiness test read that as an absent
parameter, and the `: true` branch sent the mail. There was no way to publish one quietly. It is a
fault against current upstream, not our own: the ordinary assignment has compared against `null` all
along, a few hundred lines away in `AssignmentsPresenter`. **It belongs upstream** -- see the section
below on what is patched here and should not be.

`InstancesPresenter::actionUpdateLicence()` had the same construction on `isValid`, with the
consequence in the other direction: a licence could be switched on through the API but never off.
Fixed in the same commit. Those two were the only occurrences of the pattern in `app/`.

**Measured on the running stack, both directions and against the old code.** Mail was made
observable without letting any of it out: `emails.debugMode` skips SMTP entirely and
`emails.archivingDir` still writes a copy of every message the system composes, so the archive
answers "was a notification produced" rather than "was it delivered". A throwaway group with one
enrolled student, and a shadow assignment published into it three times:

| Code     | `sendNotification` | Messages produced |
| -------- | ------------------ | ----------------- |
| fixed    | `true`             | 1                 |
| fixed    | `false`            | **0**             |
| original | `false`            | 1 -- the fault    |

The third row is the fault reproduced on this same stack, by putting the truthiness test back into
the container and taking it out again. Both archived messages are the real thing, subject
`UPolníček - Nová stínová úloha`. The old and new expressions were also evaluated side by side
against all three inputs: the old one answers "send" to every one, the new one `false`, `true`,
`true`. `php -l` passes on both changed files.

> **Testing mail: do not restart the container to apply a config change.**
> `services/api/docker-entrypoint.sh` regenerates `app/config/config.local.neon` from the template on
> every start, so a restart silently reverts the edit and the run measures the *production* mail
> settings instead -- which, on a deployment with real SMTP credentials, means the test actually
> sends. Edit the generated file and clear `temp/cache/*` instead; PHP runs with
> `opcache.validate_timestamps=1`, so the next request picks it up. This was learnt the expensive
> way, by sending one notification to the seeded `admin@admin.com` before the rig was right.

---

## Verified set — 2026-09-20 (roster import)

| Component  | Source                    | Commit     | Dated      |
| ---------- | ------------------------- | ---------- | ---------- |
| `api`      | `UPOL-KMI/upcode-api`     | `c86522e`  | 2026-09-20 |
| `worker`   | `UPOL-KMI/upcode-worker`  | `cf26d8c`  | 2026-09-17 |
| `isolate`  | `UPOL-KMI/upcode-isolate` | `bfdcf98`  | 2026-09-17 |
| `monitor`  | `UPOL-KMI/upcode-monitor` | `e6f8a1d`  | 2026-02-13 |
| `broker`   | `UPOL-KMI/upcode-broker`  | `abdc95c`  | 2022-12-04 |
| `cleaner`  | `UPOL-KMI/upcode-cleaner` | `0a5e390`  | 2025-07-16 |
| `web-next` | `UPOL-KMI/upcode-web-ui`  | `cf20600`  | 2026-09-20 |

**`api` is unchanged from the set above**, deliberately: the whole round is in the frontend. Only
the `web-next` image needs rebuilding.

**The roster import is offered to teachers now.** It was fenced off by a hard-coded superadmin
check, which left the one person who enrols a cohort unable to reach it. A group carries
`permissionHints.inviteStudents`, so the group-scoped import asks that; an import with no group
stays the administrator's.

**Verified on this deployment, per role and per kind of group.** Sessions were opened for the
existing accounts and the pages fetched as they render them: the teacher is offered the import on
the course he administers and refused on the organizational parent, on a course he does not teach,
and on the instance-wide import; the student is offered neither the link nor the page. As the
teacher, `POST /v1/groups/{id}/students/{userId}` really does enrol an existing account -- added,
checked in the group's own student list, then removed again, so the instance ended where it
started -- and `POST /v1/users/{id}/external-login/{service}` really is a 403 for him, with
nothing written to `external_login`.

**The ACL was measured one role per process.** `BasePermissionPolicy::$membershipCache` is static
and keyed by group id without the user id -- already written down in the set above -- and a probe
that asks four roles in one process gets the first one's answer for all four. Asked properly:
`inviteStudents` from `supervisor-student` up on a direct supervisor membership, `addStudent` from
`supervisor`, and `addStudent` is absent from the hints entirely because it takes two arguments.

**One thing the hint does not answer.** `inviteStudents` comes back `true` for an organizational
group, while core-api refuses every invitation into one, so the screen checks `organizational` and
`archived` beside the hint. Without that the button would appear on a page where every row fails.

**The spreadsheet reader was run against the operator's own STAG export** -- 36 columns, read
correctly, narrowed to six, `BENEŠ` repaired to `Beneš` -- plus 33 unit tests whose fixtures are
built byte by byte rather than committed, since a real export carries a student's personal data.
Five static checks pass (`typecheck`, `lint`, `format:check`, `build`, `test`: 394 unit tests).

**Not run: the end-to-end suite.** It still cannot be, on an instance without `[seed]` fixtures.
The new `e2e/import-roster.spec.ts` is written and unexecuted, and deliberately never presses the
import button: an invitation cannot be recalled and an enrolment cannot be undone from this app.

---

## Verified set — 2026-09-20

| Component  | Source                   | Commit     | Dated      |
| ---------- | ------------------------ | ---------- | ---------- |
| `api`      | `UPOL-KMI/upcode-api`    | `c86522e`  | 2026-09-20 |
| `worker`   | `UPOL-KMI/upcode-worker` | `cf26d8c`  | 2026-09-17 |
| `isolate`  | `UPOL-KMI/upcode-isolate`| `bfdcf98`  | 2026-09-17 |
| `monitor`  | `UPOL-KMI/upcode-monitor`| `e6f8a1d`  | 2026-02-13 |
| `broker`   | `UPOL-KMI/upcode-broker` | `abdc95c`  | 2022-12-04 |
| `cleaner`  | `UPOL-KMI/upcode-cleaner`| `0a5e390`  | 2025-07-16 |
| `web-next` | `UPOL-KMI/upcode-web-ui` | `c651c4d`  | 2026-09-20 |

**The fork's first edit to `permissions.neon`**: a *Cvičící s rozšířenými právy* who is a direct
supervisor of a group may open a subgroup in it. One appended block, so upstream's own edits to
that file rebase cleanly.

**Verified through the DI container, not by reasoning**, because nobody on this deployment held
the combination the rule is about. With no membership both supervisor roles are refused; with a
direct supervisor membership the plain supervisor is still refused and the empowered one is
allowed; marking the group as an exam or archiving it refuses both again. `GroupViewFactory` was
asked directly for `primaryAdminsIds`, since the frontend's new "My teaching" rests on that field
being on the wire and `/v1/groups` has no response schema in the spec to prove it from.

Two things the probing turned up that are not defects in this change but are now written down.
`BasePermissionPolicy::$membershipCache` is static and keyed by group id **without the user id**,
which the first probe demonstrated live -- asking about one group twice in a process returns the
first answer. And `actionAddGroup` accepts `isExam`/`isOrganizational` with no permission check of
their own, so a supervisor who may now open a subgroup may open it as an exam group, a flag they
could not set afterwards.

**Not observable on this deployment, and expected.** The operator is a direct administrator of
every group he administers, so the narrowed sidebar list equals the old one and no member reads as
inherited. The difference appears once a colleague holds supervisor membership on a shared parent,
which is the arrangement the round exists to make possible -- and that is how it was exercised:
a second account was given the role, opened its own subgroup, and its course stayed out of the
first account's menu.

---

## Verified set — 2026-09-17

| Component  | Source                   | Commit     | Dated      |
| ---------- | ------------------------ | ---------- | ---------- |
| `api`      | `UPOL-KMI/upcode-api`    | `45979ef`  | 2026-09-18 |
| `worker`   | `UPOL-KMI/upcode-worker` | `cf26d8c`  | 2026-09-17 |
| `isolate`  | `UPOL-KMI/upcode-isolate`| `bfdcf98`  | 2026-09-17 |
| `monitor`  | `UPOL-KMI/upcode-monitor`| `e6f8a1d`  | 2026-02-13 |
| `broker`   | `UPOL-KMI/upcode-broker` | `abdc95c`  | 2022-12-04 |
| `cleaner`  | `UPOL-KMI/upcode-cleaner`| `0a5e390`  | 2025-07-16 |
| `web-next` | `UPOL-KMI/upcode-web-ui` | `2ccc46b`  | 2026-09-18 |

**`worker` and `isolate` were pinned to the wrong commits, and only a real build found it.** Both
lines read "base of `upcode`" -- the commit *before* our patches -- while this machine had the
branch tips checked out from before the lock was written, so every build here used code the lock
did not name. On a clean server the pinned `isolate` is upstream 1.8.1, whose Makefile has no
`isolate-cg-keeper` target, and the worker image fails to build at that line. The 2.7 upgrade and
the worker change that goes with it (`--cg-timing`, which Isolate 2.0 removed) are on the `upcode`
tips, which is where both pins now point.

The rehearsal below had not caught it because it stopped at `docker compose config`: the pins were
cloned, the configuration parsed, and nothing compiled. It builds now.

**The web-next pin was a release behind, and the rehearsal is what said so.** It named the round
that filled the landing page's container -- the arrangement the operator rejected -- rather than the
centred column that replaced it, because the pin was bumped when that commit landed and not again
when it was superseded. A deployment built from it would have shipped the layout nobody wanted.
Bumping a pin belongs with the commit it names, and the clean-clone rehearsal is what catches it
when it does not.

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

`api` is pinned to the tip of `upcode`: the e-mail round was merged there as pull request #1, and
the pin follows the merge commit rather than the branch it came from.

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
