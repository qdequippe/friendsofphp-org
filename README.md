# [FriendsOfPhp.org](https://www.friendsofphp.org) - World PHP Meetups in next 30 days

<div align="center">
   <img src="/docs/new_website.png?v=3">
</div>

## Install

```sh
git clone git@github.com:TomasVotruba/friendsofphp.org.git # use your fork to contribute
cd friendsofphp.org
composer install
```

- Copy `.env` to `.env.local`
- Add your [Meetup.com API keys](https://secure.meetup.com/meetup_api/oauth_consumers/):

```dotenv
# .env.local
MEETUP_COM_OAUTH_KEY=...
MEETUP_COM_OAUTH_SECRET=...
```

- Update Meetup Data

```bash
bin/console import
```

- Run Local Server

```bash
bin/console server:run
```

You'll find new or updated files in `/config/_data/` directory.

## JSON API?

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
