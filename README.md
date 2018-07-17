# [FriendsOfPhp.org](https://www.friendsofphp.org)

[![Build Status](https://img.shields.io/travis/TomasVotruba/friendsofphp.org/master.svg?style=flat-square)](https://travis-ci.org/TomasVotruba/friendsofphp.org)
[![Coverage Status](https://img.shields.io/coveralls/TomasVotruba/friendsofphp.org/master.svg?style=flat-square)](https://coveralls.io/github/TomasVotruba/friendsofphp.org?branch=master)

## Install

```sh
git clone ...
cd ...
npm install
```

### Update Data

```markdown
bin/fop import-groups-from-php-ug # imports groups from http://php.ug
bin/fop import-meetups-from-meetups-com # based on groups, import meetups from http://php.ug
```

## Run the website

Now all you gotta do it move to the directory and run the gulp (see [gulpfile.js](/gulpfile.js) for more details):

```sh
gulp
```

And open [http://localhost:8000](localhost:8000) in your browser.

That's all!
