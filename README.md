# [FriendsOfPhp.org](https://www.friendsofphp.org) - All PHP Meetups in next 30 days

[![Build Status](https://img.shields.io/travis/TomasVotruba/friendsofphp.org/master.svg?style=flat-square)](https://travis-ci.org/TomasVotruba/friendsofphp.org)

<div align="center">
   <img src="/docs/preview.png?v=1">
</div>

## Install

```sh
git clone git@github.com:TomasVotruba/friendsofphp.org.git # use your fork to contribute
cd friendsofphp.org
composer install
npm install
gulp # see gulpfile.js for more
```

### Update Meetup Data

To see some meetups you must import them first:

```bash
bin/console import

# then run website
gulp
```

You'll find new or updated files in `/source/_data/generated/` directory.

## API?

Do you want to get all groups and meetups? There you are:

- https://friendsofphp.org/api/meetups.json 
- https://friendsofphp.org/api/groups.json

## Maintenance

How to keep fit and slim!

### Check Status Code of All Links

Once in a few months, check if all external links are still alive, so people won't get lost.

```bash
composer dead
```
