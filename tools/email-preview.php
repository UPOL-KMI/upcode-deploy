<?php

/**
 * Render every email template to an HTML file, so the wording can be read before it is sent.
 *
 * Run inside the `api` container (`tools/email-preview.sh` does that for you). It renders each
 * message the way `EmailHelper::send()` does -- the notification template first, its output as the
 * `message` of the shared shell -- so what lands in the output directory is byte for byte what a
 * reader would receive, links included.
 *
 * **The sample data is fictional and the addresses are the deployment's real ones.** That is the
 * point: the fastest way to find out where a link in a footer actually goes is to open the thing
 * the reader opens.
 *
 * **The placeholder tokens are deliberately not JWT-shaped.** They used to begin with the base64 of
 * a JWT header, which is what a real one begins with -- and a secret scanner cannot tell the two
 * apart, nor should it try. GitGuardian raised exactly that against this repository (a false
 * positive: the string was invented here and signs nothing). A false positive that fires on every
 * clone costs somebody an investigation each time, so the placeholder now says in words what it is.
 * Do not make it look realistic again.
 *
 * Adding a template: add an entry to $SAMPLES keyed by its path under app/helpers/Emails. A
 * template with no entry is rendered with an empty parameter set and will most likely fail, which
 * the summary at the end reports rather than hides.
 */

declare(strict_types=1);

require __DIR__ . "/../vendor/autoload.php";

use App\Helpers\Emails\EmailLatteFactory;
use Nette\Loaders\RobotLoader;
use Nette\Neon\Neon;

// core-api's composer autoloader does not map `App\` -- the application finds its own classes
// through Nette's RobotLoader, configured in the DI container this script does not boot. So the
// same loader is started here by hand, over `app/` only.
$loader = new RobotLoader();
$loader->addDirectory(__DIR__ . "/../app");
$loader->setTempDirectory(__DIR__ . "/../temp/preview-loader");
$loader->register();

$root = __DIR__ . "/..";
$out = $argv[1] ?? "/tmp/email-preview";

// Where the .latte files are read from. Defaults to the ones this container was built with; the
// wrapper script points it at the *working tree* instead, because the whole purpose of a preview
// is to read wording that has not been deployed yet. The PHP classes still come from the image --
// only the templates are being edited.
$templates = $argv[2] ?? "$root/app/helpers/Emails";

// --- the shell's own values, read from the running deployment's config ---------------------------
//
// Not hardcoded: the whole reason to look at a preview is to see the real addresses, and a preview
// that invented them would answer the wrong question.
// **Both files, in that order.** The deployment's `config.local.neon` overrides only what it sets;
// everything else -- `emails.apiUrl` above all, which is where the header's logo is fetched from --
// stays at core-api's own default in `config.neon`. Reading only the local file left `apiUrl`
// empty, so the preview showed a relative `src` and no logo, on templates whose logo was fine.
$base = Neon::decode(file_get_contents("$root/app/config/config.neon"));
$local = Neon::decode(file_get_contents("$root/app/config/config.local.neon"));
$parameters = array_replace_recursive($base["parameters"] ?? [], $local["parameters"] ?? []);
$emails = $parameters["emails"] ?? [];

/** Expands `%webapp.address%` and friends against the same config file. */
function expand(string $value, array $parameters): string
{
    return preg_replace_callback("/%([a-zA-Z]+)\.([a-zA-Z]+)%/", function ($m) use ($parameters) {
        return $parameters[$m[1]][$m[2]] ?? $m[0];
    }, $value);
}

$shell = [
    "apiUrl" => expand((string)($emails["apiUrl"] ?? ""), $parameters),
    "footerUrl" => expand((string)($emails["footerUrl"] ?? ""), $parameters),
    "siteName" => (string)($emails["siteName"] ?? "ReCodEx"),
    "githubUrl" => (string)($emails["githubUrl"] ?? ""),
];

$webapp = $shell["footerUrl"];

// --- sample data --------------------------------------------------------------------------------
$now = new DateTime("2026-09-20 23:59:00");
$soon = new DateTime("2026-09-27 23:59:00");
$then = new DateTime("2026-09-15 14:32:00");

/** A review comment, as the templates use one. */
function comment(?string $file, ?int $line, bool $issue, string $text): object
{
    return new class ($file, $line, $issue, $text) {
        public function __construct(
            private ?string $file,
            private ?int $line,
            private bool $issue,
            private string $text
        ) {
        }
        public function getFile(): ?string
        {
            return $this->file;
        }
        public function getLine(): ?int
        {
            return $this->line;
        }
        public function isIssue(): bool
        {
            return $this->issue;
        }
        public function getText(): string
        {
            return $this->text;
        }
    };
}

$ASSIGNMENT = "Ackermannova funkce";
$GROUP = "Algoritmy a datové struktury — cvičení út 9:45";
$EXERCISE = "Ackermannova funkce";
$SOLUTION_URL = "$webapp/cs/solutions/2b9c4d6a-7e18-4f30-a5b2-1c8e9f0d3a76";
$ASSIGNMENT_URL = "$webapp/cs/assignments/6f2a1b8c-3d54-4e97-8a10-5b7c2d9e4f61";

$review = [
    "assignment" => $ASSIGNMENT,
    "group" => $GROUP,
    "attempt" => 2,
    "submitted" => $then,
    "closed" => $now,
    "codeUrl" => "$SOLUTION_URL/sources",
    "detailUrl" => $SOLUTION_URL,
];

$flag = [
    "attempt" => 2,
    "attempts" => 3,
    "assignment" => $ASSIGNMENT,
    "group" => $GROUP,
    "submittedAt" => $then,
    "prevAttempt" => 1,
    "prevSubmittedAt" => new DateTime("2026-09-12 10:05:00"),
    "link" => $SOLUTION_URL,
];

$SAMPLES = [
    "EmailVerificationHelper/verificationEmail" => [
        "email" => "student@upol.cz",
        "link" => "$webapp/cs/email-verification?token=THE-TOKEN-FROM-THE-REAL-MESSAGE",
        "expiresAfter" => "20.9.2026 23:59",
        "firstTime" => true,
    ],
    "ForgottenPasswordHelper/resetPasswordEmail" => [
        "username" => "Jan Novák",
        "link" => "$webapp/cs/forgot-password/change?token=THE-TOKEN-FROM-THE-REAL-MESSAGE",
        "expiresAfter" => "20.9.2026 23:59",
    ],
    "InvitationHelper/invitationEmail" => [
        "username" => "Jan Novák",
        "host" => "Mgr. Jakub Juračka",
        "hostmail" => "jakub.juracka@upol.cz",
        "expireAt" => $soon,
        "link" => "$webapp/cs/accept-invitation?token=THE-TOKEN-FROM-THE-REAL-MESSAGE",
    ],

    "Notifications/AssignmentPoints/shadowPointsUpdated" => [
        "assignment" => $ASSIGNMENT,
        "group" => $GROUP,
        "points" => 8,
        "maxPoints" => 10,
        "link" => "$webapp/cs/shadow-assignments/1a2b3c4d-5e6f-4a7b-8c9d-0e1f2a3b4c5d",
    ],
    "Notifications/AssignmentPoints/solutionPointsUpdated" => [
        "assignment" => $ASSIGNMENT,
        "group" => $GROUP,
        "points" => 9,
        "maxPoints" => 10,
        "hasBonusPoints" => true,
        "bonusPoints" => 2,
        "link" => $SOLUTION_URL,
    ],

    "Notifications/Assignments/assignmentDeadline" => [
        "assignment" => $ASSIGNMENT,
        "group" => $GROUP,
        "firstDeadline" => $now,
        "allowSecondDeadline" => true,
        "secondDeadline" => $soon,
        "link" => $ASSIGNMENT_URL,
    ],
    "Notifications/Assignments/newAssignmentEmail" => [
        "assignment" => $ASSIGNMENT,
        "group" => $GROUP,
        "firstDeadline" => $now,
        "allowSecondDeadline" => true,
        "secondDeadline" => $soon,
        "attempts" => 5,
        "points" => 10,
        "link" => $ASSIGNMENT_URL,
    ],
    "Notifications/Assignments/newShadowAssignmentEmail" => [
        "name" => "Cvičení 3 — materiály k procvičení",
        "group" => $GROUP,
        "maxPoints" => 5,
        "siteName" => $shell["siteName"],
        "link" => "$webapp/cs/shadow-assignments/1a2b3c4d-5e6f-4a7b-8c9d-0e1f2a3b4c5d",
    ],
    "Notifications/Assignments/shadowAssignmentDeadline" => [
        "assignment" => "Cvičení 3 — materiály k procvičení",
        "group" => $GROUP,
        "deadline" => $now,
        "link" => "$webapp/cs/shadow-assignments/1a2b3c4d-5e6f-4a7b-8c9d-0e1f2a3b4c5d",
    ],

    "Notifications/AsyncJobs/asyncJobsStuck" => [
        "count" => 3,
        // A DateInterval, not a number of seconds: the `relativeDateTime` filter this
        // template runs it through takes one, and a plain int is a TypeError.
        "maxDelay" => new DateInterval("PT30M"),
    ],

    "Notifications/Comments/assignmentComment" => [
        "assignment" => $ASSIGNMENT,
        "group" => $GROUP,
        "author" => "Jan Novák",
        "comment" => "Má se rekurze počítat i pro záporná čísla?",
        "date" => $then,
        "otherComments" => 2,
        "link" => "$ASSIGNMENT_URL?tab=discussion",
    ],
    "Notifications/Comments/assignmentSolutionComment" => [
        "assignment" => $ASSIGNMENT,
        "author" => "Mgr. Jakub Juračka",
        "solutionAuthor" => "Jan Novák",
        "comment" => "Hezké řešení, jen ta paměťová složitost by šla zlepšit.",
        "date" => $then,
        "otherComments" => 0,
        "link" => $SOLUTION_URL,
    ],
    "Notifications/Comments/referenceSolutionComment" => [
        "exercise" => $EXERCISE,
        "author" => "Mgr. Jakub Juračka",
        "solutionAuthor" => "Mgr. Jakub Juračka",
        "comment" => "Tohle referenční řešení schválně nepoužívá memoizaci.",
        "date" => $then,
        "otherComments" => 1,
        "link" => "$webapp/cs/exercises/9e8d7c6b-5a49-4382-b1c0-d9e8f7a6b5c4",
    ],

    "Notifications/Exercises/exerciseNotification" => [
        "exercise" => $EXERCISE,
        "user" => "Mgr. Jakub Juračka",
        "email" => "jakub.juracka@upol.cz",
        "message" => "Opravil jsem třetí test, měl špatně očekávaný výstup.",
        "link" => "$webapp/cs/exercises/9e8d7c6b-5a49-4382-b1c0-d9e8f7a6b5c4",
    ],

    "Notifications/Reviews/commentNew" => $review + [
        "comment" => comment("main.py", 14, true, "Tady se index přeteče pro `n = 0`."),
    ],
    "Notifications/Reviews/commentRemoved" => $review + [
        "comment" => comment("main.py", 14, true, "Tady se index přeteče pro `n = 0`."),
    ],
    "Notifications/Reviews/commentUpdated" => $review + [
        "comment" => comment("main.py", 14, false, "Tady se index přeteče pro `n = 0`. **Opraveno.**"),
        "oldText" => "Tady se index přeteče pro `n = 0`.",
        "issueChanged" => true,
    ],
    "Notifications/Reviews/reviewClosed" => $review + [
        "summary" => [comment(null, null, false, "Celkově dobré. Zkuste příště **memoizaci**.")],
        "issues" => [comment("main.py", 14, true, "Tady se index přeteče pro `n = 0`.")],
        "comments" => [comment("main.py", 3, false, "Pojmenování proměnných je srozumitelné.")],
    ],
    "Notifications/Reviews/reviewRemoved" => $review,
    "Notifications/Reviews/reviewReopened" => $review,
    "Notifications/Reviews/pendingReviews" => [
        "solutions" => [
            (object)[
                "assignment" => $ASSIGNMENT,
                "group" => $GROUP,
                "attempt" => 2,
                "submitted" => $then,
                "solutionUrl" => $SOLUTION_URL,
                "assignmentUrl" => $ASSIGNMENT_URL,
            ],
            (object)[
                "assignment" => "Binární vyhledávací strom",
                "group" => $GROUP,
                "attempt" => 1,
                "submitted" => new DateTime("2026-09-14 09:12:00"),
                "solutionUrl" => "$webapp/cs/solutions/8c1d5e3f-4a29-4b71-9e02-6d7b3c8a1f54",
                "assignmentUrl" => "$webapp/cs/assignments/4d3c2b1a-9f8e-4d7c-b6a5-948372615049",
            ],
        ],
    ],

    "Notifications/Solutions/solutionFlagChangedAccepted" => $flag + [
        "accepted" => true,
        "points" => 9,
        "maxPoints" => 10,
    ],
    "Notifications/Solutions/solutionFlagChangedReviewRequest" => $flag + [
        "requested" => true,
        "author" => "Jan Novák",
    ],

    "Notifications/Submissions/newSubmissionAfterAcceptance" => [
        "assignment" => $ASSIGNMENT,
        "user" => "Jan Novák",
        "score" => 100,
        "link" => $SOLUTION_URL,
    ],
    "Notifications/Submissions/newSubmissionAfterReview" => [
        "assignment" => $ASSIGNMENT,
        "user" => "Jan Novák",
        "score" => 90,
        "link" => $SOLUTION_URL,
    ],
    "Notifications/Submissions/submissionEvaluated" => [
        "assignment" => $ASSIGNMENT,
        "group" => $GROUP,
        "date" => $then,
        "status" => "Vyhodnoceno",
        "points" => 9,
        "maxPoints" => 10,
        "isResubmit" => true,
        "submittedBy" => "Mgr. Jakub Juračka",
        "link" => $SOLUTION_URL,
    ],

    "Notifications/failureResolved" => [
        "title" => $ASSIGNMENT,
        "date" => $then,
        "note" => "Sandbox nemohl spustit Python, chyba v nastavení workeru. Opraveno, řešení přehodnoceno.",
    ],
    "Notifications/generalStats" => [
        "period" => "uplynulý týden",
        "createdUsers" => 12,
        "totalUsers" => 431,
        "inactiveUsers" => 38,
        "createdGroups" => 2,
        "totalGroups" => 27,
        "archivedGroups" => 4,
        "createdExercises" => 3,
        "totalExercises" => 58,
        "createdAssignments" => 9,
        "totalAssignments" => 214,
        "createdSolutions" => 186,
        "totalSolutions" => 4038,
        "createdSubmissions" => 203,
        "totalEvaluations" => 4211,
        "failedSubmissions" => 2,
    ],
];

// --- render ---------------------------------------------------------------------------------------
@mkdir($out, 0o777, true);

$latte = EmailLatteFactory::latte();
$base = rtrim($templates, "/");
$shellTemplates = [
    "cs" => "$base/EmailHelper/email_cs.latte",
    "en" => "$base/EmailHelper/email_en.latte",
];

$written = 0;
$failed = [];
$rendered = [];

foreach (glob("$base/**/*.latte") ?: [] as $ignored) {
    // glob's ** is not recursive; the real walk is below.
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
$templates = [];
foreach ($iterator as $file) {
    if ($file->getExtension() !== "latte") {
        continue;
    }
    $path = substr($file->getPathname(), strlen($base) + 1);
    if (str_starts_with($path, "EmailHelper/")) {
        continue;  // the shell itself, rendered around every other one
    }
    $templates[] = $path;
}
sort($templates);

foreach ($templates as $path) {
    // `Notifications/Reviews/reviewClosed_cs.latte` -> key + locale
    $stem = preg_replace('/\.latte$/', "", $path);
    $locale = "cs";
    if (preg_match('/_(cs|en)$/', $stem, $m)) {
        $locale = $m[1];
        $stem = substr($stem, 0, -3);
    }

    $params = $SAMPLES[$stem] ?? null;
    if ($params === null) {
        $failed[$path] = "no sample data for '$stem'";
        continue;
    }

    try {
        $inner = $latte->renderEmail("$base/$path", $params + $shell);
        $html = $latte->renderEmail($shellTemplates[$locale], $shell + [
            "subject" => $inner->getSubject(),
            "message" => $inner->getText(),
            "showSettingsInfo" => true,
        ])->getText();
    } catch (Throwable $e) {
        $failed[$path] = get_class($e) . ": " . $e->getMessage();
        continue;
    }

    $name = str_replace("/", "__", preg_replace('/\.latte$/', "", $path)) . ".html";
    file_put_contents("$out/$name", $html);
    $written++;

    // The subject line is not in the HTML anywhere -- it is the mail header -- so it is listed
    // separately or there is no way to review it.
    file_put_contents("$out/SUBJECTS.txt", sprintf("%-60s %s\n", $name, $inner->getSubject()), FILE_APPEND);
    $rendered[$name] = $inner->getSubject();
}

// --- an index, so the whole set can be clicked through ------------------------------------------
$items = [];
foreach ($rendered as $name => $subject) {
    $label = str_replace("__", " › ", preg_replace('/\.html$/', "", $name));
    $items[] = sprintf(
        '<li><a href="%s" target="preview"><span class="t">%s</span><span class="s">%s</span></a></li>',
        htmlspecialchars($name, ENT_QUOTES),
        htmlspecialchars($label, ENT_QUOTES),
        htmlspecialchars($subject, ENT_QUOTES)
    );
}
$list = implode("\n", $items);
$first = htmlspecialchars((string)array_key_first($rendered), ENT_QUOTES);
$count = count($rendered);

file_put_contents("$out/index.html", <<<HTML
<!doctype html>
<meta charset="utf-8">
<title>Náhledy e-mailů — {$shell["siteName"]}</title>
<style>
  :root { color-scheme: light; }
  * { box-sizing: border-box; }
  body { margin:0; display:flex; height:100vh; font:14px -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; color:#14181c; }
  nav { width:340px; flex:none; overflow-y:auto; border-right:1px solid #e3e8ec; background:#f6f8fa; }
  nav h1 { margin:0; padding:16px; font-size:14px; border-bottom:1px solid #e3e8ec; background:#fff; }
  nav h1 small { display:block; font-weight:400; color:#5b6670; margin-top:2px; }
  ul { list-style:none; margin:0; padding:0; }
  a { display:block; padding:10px 16px; text-decoration:none; color:inherit; border-bottom:1px solid #eef1f4; }
  a:hover { background:#e9eef2; }
  a.on { background:#016BAB; color:#fff; }
  a.on .s { color:#cfe6f5; }
  .t { display:block; font-size:12px; color:#5b6670; }
  a.on .t { color:#bcdcf0; }
  .s { display:block; font-weight:600; margin-top:2px; }
  iframe { flex:1; border:0; background:#fff; }
</style>
<nav>
  <h1>Náhledy e-mailů<small>{$count} zpráv · vygenerováno z pracovního stromu</small></h1>
  <ul>{$list}</ul>
</nav>
<iframe name="preview" src="{$first}"></iframe>
<script>
  const links = [...document.querySelectorAll('nav a')];
  links[0].classList.add('on');
  document.querySelector('nav').addEventListener('click', e => {
    const a = e.target.closest('a');
    if (!a) return;
    links.forEach(l => l.classList.remove('on'));
    a.classList.add('on');
  });
</script>
HTML);

echo "Wrote $written previews to $out\n";
foreach ($failed as $path => $why) {
    fwrite(STDERR, "FAILED  $path  --  $why\n");
}
exit($failed ? 1 : 0);
