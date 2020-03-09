# [FriendsOfPhp.org](https://www.friendsofphp.org) - All PHP Meetups in next 30 days

[![Build Status Github Actions](https://img.shields.io/github/workflow/status/tomasvotruba/friendsofphp.org/Code_Checks?style=flat-square)](https://github.com/TomasVotruba/friendsofphp.org/actions)

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

Create `config/config.local.yaml` and add your [Meetup.com API keys](https://secure.meetup.com/meetup_api/oauth_consumers/):

```yaml
# config/config.local.yaml
parameters:
    env(MEETUP_COM_OAUTH_KEY): "..."
    env(MEETUP_COM_OAUTH_SECRET): "..."
```

This is needed to import meetups from meetup.com groups.

### Update Meetup Data

To see some meetups you must import them first:

```bash
bin/console import

# then run website
gulp
```

You'll find new or updated files in `/source/_data/generated/` directory.

To **upgrade last meeting dates**:

```bash
bin/console last-group-meetup
```

Don't forget to use the API keys:

```
MEETUP_COM_OAUTH_KEY=xxx MEETUP_COM_OAUTH_SECRET=yyy bin/console last-group-meetup
```

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
