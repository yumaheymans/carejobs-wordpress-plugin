=== CareJobs Healthcare Jobs ===
Contributors: carejobs
Tags: jobs, healthcare, nursing, job board, careers
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show live open US healthcare jobs with posted pay on your site. Filter by kind of job, state and keywords. Free, no account, no API key.

== Description ==

CareJobs Healthcare Jobs puts a live list of open US healthcare jobs on any WordPress site: a nursing school, a healthcare association, a staffing blog, a local news site. Each job shows its title, employer, the pay the employer posts, the place and the date it was listed, and links to the job's page on [CareJobs.Now](https://carejobs.now), where the reader applies.

CareJobs.Now is a free US healthcare job board. This plugin needs no account and no API key. Install it, add the block, shortcode or widget, and the list appears.

**Show it three ways**

* **Block.** In the editor, add the "Healthcare Jobs" block (category Widgets) and choose the filters in the sidebar.
* **Shortcode.** `[carejobs_jobs kind="nurses" state="TX" limit="10"]`
* **Widget.** Appearance, Widgets, "CareJobs Healthcare Jobs".

**Shortcode attributes**

All are optional.

* `kind`: the kind of job. One of the slugs listed below. Empty means all kinds.
* `state`: a US state, as a two-letter code (`TX`) or a name (`Texas`). Empty means all states. The 50 states and DC are supported.
* `query`: words every job must carry in its title, place, department or schedule, such as `ICU`, `night`, `travel` or `Houston`.
* `limit`: how many jobs to show, 1 to 25. Default 10.
* `sort`: `newest` (default), `highest_pay` or `mixed` (employers interleaved).
* `remote`: set to `1` to show only remote jobs, such as telehealth, case management, coding and billing.
* `min_pay`: only jobs whose posted hourly pay reaches this many US dollars.
* `title`: an optional heading above the list.
* `new_tab`: set to `1` to open job links in a new tab.

**Kinds of job (`kind`)**

* `nurses`: Nurses
* `nurse-practitioners`: NPs and PAs
* `physicians`: Doctors
* `nursing-assistants`: CNAs and PCTs
* `caregivers`: Caregivers
* `therapy-rehab`: Therapy and rehab
* `mental-health`: Mental health
* `pharmacy`: Pharmacy
* `imaging`: Imaging
* `lab`: Lab
* `surgical-techs`: Surgical
* `emts-paramedics`: EMS
* `medical-assistants`: Medical assistants
* `dental`: Dental
* `admin`: Front desk and admin
* `allied-health`: Allied health
* `ai-training`: AI training

**Examples**

* Night shift ICU nurses in Texas: `[carejobs_jobs kind="nurses" state="TX" query="ICU night" title="ICU jobs in Texas"]`
* The best paid dental jobs: `[carejobs_jobs kind="dental" sort="highest_pay" limit="5"]`
* Remote healthcare jobs paying at least $30 an hour: `[carejobs_jobs remote="1" min_pay="30"]`

**Good to know**

* The list is cached for one hour, so your pages stay fast and the service is not called on every visit.
* Pay is what employers post. When a posting does not state pay, the list may show a typical figure for that employer, marked "typical at" the employer's name.
* The plugin loads one small stylesheet, and only on pages that show a list. It loads no scripts, fonts or images from other sites, sets no cookies and has no tracking.
* All text from the service is sanitized and escaped, and job links are only shown if they point to carejobs.now.

== External services ==

This plugin connects to the CareJobs.Now API (https://carejobs.now/api/mcp) to fetch the job listings it displays.

**What is sent.** Only the filters the site owner chose in the block, shortcode or widget: kind of job, state, keywords, number of jobs, sort order, remote only and minimum hourly pay. No visitor data is sent: no IP address, cookie, browser details or any other information about the people who view your pages. The request comes from your web server, so, as with any web request, your server's IP address is visible to CareJobs.Now. The plugin identifies itself only as "CareJobsHealthcareJobs" with its version number, not with your site address.

**When.** When a page that contains the shortcode, block or widget is rendered and the one-hour cache for that list is empty. If the service cannot be reached, the plugin waits five minutes before trying again for that list.

**Who runs it.** CareJobs.Now. Its terms of service are at https://carejobs.now/legal/terms and its privacy policy is at https://carejobs.now/legal/privacy.

Each job in the list links to its page on carejobs.now. That is a normal link: nothing is sent to CareJobs.Now unless a reader chooses to follow it.

== Installation ==

1. In your WordPress admin, go to Plugins, Add New, and search for "CareJobs Healthcare Jobs". Or upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin.
3. Edit a page and add the "Healthcare Jobs" block, or type a shortcode such as `[carejobs_jobs kind="nurses" state="TX"]`, or add the "CareJobs Healthcare Jobs" widget under Appearance, Widgets.

There is nothing to configure and no account to create.

== Frequently Asked Questions ==

= Does it cost anything? Do I need an account or an API key? =

No. The plugin and the job data are free. There is no account, no key and no upgrade.

= What do readers see, and where do they apply? =

Each job shows its title (linked), employer, pay (when posted), place and date listed. The link goes to the job's page on CareJobs.Now, where the reader applies. A "See all jobs on CareJobs.Now" link below the list shows everything that matches the same filters.

= Which jobs are shown? =

Open healthcare jobs at US hospitals, health systems, clinic groups and care companies, plus listings from job boards that CareJobs.Now reads, each naming its employer. Use `kind`, `state`, `query`, `remote` and `min_pay` to narrow the list.

= How often is the list updated? =

CareJobs.Now keeps its listings current. Your site keeps each list for one hour before asking again.

= What happens if CareJobs.Now cannot be reached? =

The list is replaced by the message "Healthcare jobs could not be loaded right now." and the plugin tries again after five minutes. Nothing breaks, and the rest of the page loads normally.

= Which value do I use for `kind`? =

One of the slugs in the Description above, for example `nurses`, `nursing-assistants` or `dental`. Editors who are logged in see a short note on the page if a value is not recognized, and the value is ignored. Visitors never see that note.

= Does it track my visitors or add scripts to my pages? =

No. It sends nothing about visitors, sets no cookies and loads no external scripts, fonts or images. See "External services" for exactly what is sent and when.

= Can I change how it looks? =

The list inherits your theme's fonts and colors. Add your own CSS to the classes that start with `carejobs-hj` (for example `.carejobs-hj__item`, `.carejobs-hj__link` and `.carejobs-hj__pay`).

= Does it work outside the US? =

The jobs are US healthcare jobs. The plugin works on any WordPress site, wherever it is hosted.

== Screenshots ==

1. A list of open registered nurse jobs in Texas, shown with the shortcode on a page.

== Changelog ==

= 1.0.0 =
* Initial release: shortcode, block and widget showing open US healthcare jobs from CareJobs.Now, with filters for kind of job, state, keywords, remote and minimum pay.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
