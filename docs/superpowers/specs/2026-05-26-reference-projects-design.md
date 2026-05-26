# Reference Projects Design

## Goal

Add two new portfolio reference projects to the homepage project carousel in both Hungarian and English.

## Scope

The existing homepage renders project cards from `config/portfolio.php`. The change should add:

- `TiszaszalkaSE.com` as a sports/community website with automatic MLSZ result updates.
- `NapiInfo.com` as a strong flagship project: a fully automated AI-powered Laravel news site, described as the concentration of current monetization and automation knowledge.

No layout, CSS, or JavaScript changes are needed because the carousel already supports additional items.

## Content Direction

`NapiInfo.com` should sound ambitious and technical without becoming vague marketing copy. It should mention AI, Laravel, full automation, monetization knowledge, and the idea that the system works and learns on its own.

`TiszaszalkaSE.com` should sound practical and trustworthy. It should mention the automatic pull of match results from the MLSZ website.

## Testing

Add a feature test that checks the Hungarian and English homepages render both new project names and links.
