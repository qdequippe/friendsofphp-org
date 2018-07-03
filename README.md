# [FriendsOfPhp.org](https://www.friendsofphp.org)

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
