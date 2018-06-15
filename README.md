# [FriendsOfPhp.org](https://www.friendsofphp.org)

## Install

```sh
composer create-project --todo/--todo @dev
npm install
```

## Run the website

Now all you gotta do it move to the directory and run the gulp (see [gulpfile.js](/gulpfile.js) for more details):

```sh
cd --todo
gulp
```

And open [http://localhost:8000](localhost:8000) in your browser.

That's all!


## Check The Grammar

With help of [vlajos/misspell-fixer](https://github.com/vlajos/misspell-fixer) package, that has thousands of rules, fixes them all for you and still - is super fast!

```bash
# install
cd www
git clone https://github.com/vlajos/misspell-fixer
cd tomasvotruba.cz

# use
../misspell-fixer/misspell-fixer -suRVDrn source/_posts
```

## Check Status Code of All Links

```bash
vendor/bin/http-status-check scan https://tomasvotruba.cz
```