# FriendsOfFlarum Merge Discussions

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/fof/merge-discussions.svg)](https://packagist.org/packages/fof/merge-discussions)

A [Flarum](http://flarum.org) extension. Merge two or more discussions into one.

### Installation

Install with composer:

```sh
composer require fof/merge-discussions:"*"
```

### Updating

```sh
composer update fof/merge-discussions
php flarum migrate
php flarum cache:clear
```

### Features

- **Merge from either side**: from a discussion's controls, merge other discussions into it, or merge it into another. This needs the *Merge discussions* permission, under *Moderate*.
- **Choose the post order**: interleave all posts by their creation date, or add the merged posts to the end of the discussion.
- **Preview first**: see the resulting discussion before merging.
- **A record in the discussion**: the merge adds an event post, such as "Merged 3 posts from …".
- **Authors are notified**: the author of each merged discussion gets an alert and an email, which they can turn off in their notification preferences.
- **Old links keep working**: a link to a merged discussion redirects permanently (301) to the discussion it was merged into, and a link to a post in it goes to that post's new position, at its canonical URL. Visitors who can't see the target discussion get a 404 instead. Links followed inside the forum go to the same place.
- **Audit log**: with [flarum/audit](https://packagist.org/packages/flarum/audit) enabled, merges are recorded in the audit log.
- **Search limit**: set how many discussions the merge modal's search returns.

### Links

- [Packagist](https://packagist.org/packages/fof/merge-discussions)
- [GitHub](https://github.com/FriendsOfFlarum/merge-discussions)

An extension by [FriendsOfFlarum](https://github.com/FriendsOfFlarum), commissioned by [giffgaff](https://community.giffgaff.com).
