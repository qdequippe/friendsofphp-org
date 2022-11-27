## Sunsetting Due to Paid Access from Meetup.com

The meetup.com API is now behind a paywall since November 2022. This means this project takes 30 $+ a month to run. Also, the API changed from REST to GraphQL and the documentation does not allow to fetch meetups by array of groups anymore.

I've check the visits per month to verify if it's worth to ask Meetup com support to clarify the docs, and it's around 2-5 people a day:

<img src="/docs/visits.png">

Saying this, it seems this project is not worth to keep it running. I'll keep the code here for reference, but I'll not be able to maintain it anymore.

Thanks for understanding

---

<div align="center">
    <h1>
        <a href="https://www.friendsofphp.org">Friends of Php.org</a>
    </h1>
    <h2>World PHP Meetups in next 30 days</h2>
</div>

<br>

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

Happy coding... with friends!
