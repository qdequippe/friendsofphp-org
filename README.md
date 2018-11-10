# [FriendsOfPhp.org](https://www.friendsofphp.org) - Every PHP Meetup in next 30 days in 1 Place

[![Build Status](https://img.shields.io/travis/TomasVotruba/friendsofphp.org/master.svg?style=flat-square)](https://travis-ci.org/TomasVotruba/friendsofphp.org)

<div align="center">
   <img src="/docs/preview.png">
</div>

## Install

```sh
git clone git@github.com:TomasVotruba/friendsofphp.org.git # use your fork to contribute
cd friendsofphp.org
composer intalll
npm install
gulp # see gulpfile.js for more
```

### Update Meetup Data

To see new meetups, import them first:

```bash
bin/console import

# then run website
gulp
```

You'll find new or updated files in `/source/_data/generated/` directory.

## Maintenance

How to keep fit and slim!

### Check Status Code of All Links

Once couple of months, check if all external links are still alive, so people won't get lost.

```bash
vendor/bin/http-status-check scan https://friendsofphp.org/groups/
```
