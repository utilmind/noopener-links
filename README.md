# Noopener Links

WordPress plugin. Used to process links in the posts and fix them, according to PCI compliance.

What it doing with WP-posts:
 * Add `rel="noopener"` and `target="_blank"` for EXTERNAL links;
 * Remove rel="noopener" and target="_blank" from INTERNAL (local) links;
 * Detect local domains to strip them from the URL prefixes, to make shorter local links.
