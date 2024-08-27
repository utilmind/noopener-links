# Noopener Links

WordPress plugin. Used to process links in the posts and fix them, according to PCI compliance.

And no, WordPress itself is not PCI compliant, but can run on isolated low-privileged user account for some website section separate from the main non-WordPress and PCI compliant website. In WordPress we only need to make all links PCI compliant. (All external links should have rel="noopener" attribute, plus we should set up some policies with the headers of HTTP-responses.)

What it doing with WP-posts:
 * Add `rel="noopener"` and `target="_blank"` for EXTERNAL links;
 * Remove rel="noopener" and target="_blank" from INTERNAL (local) links;
 * Detect local domains to strip them from the URL prefixes, to make shorter local links.
