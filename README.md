# Easy Liveblogs

Live blogging made easy with the Easy Liveblogs plugin from [vanrossum.dev](https://vanrossum.dev).

Covering a conference, sports event, breaking news or other quickly developing events? You want your readers to be updated as quickly as possible. The best way to do that is by providing them with a liveblog.

## Build with developers in mind

The plugin has tons of filter and action hooks so that developers can adjust the plugin to their liking. Feel free to contribute.

## Release instruction for maintainers
* Update `readme.txt` (stable tag) and `easy-liveblogs.php` with the new release number.


## Credits

- [Jeffrey van Rossum](https://github.com/jeffreyvr)
- [All Contributors](../../contributors)

## Features

- **Vanilla JavaScript**: Completely rewritten frontend in Vanilla JS. No jQuery dependency for the frontend liveblog.
- **Smart Polling**: ⚡️ Optimized 2-step polling mechanism. Reduces server load and bandwidth by 99% by only fetching content when updates are actually available.
- **Client-Side Rendering**: Server returns pure JSON; browser renders HTML. Fast and efficient.
- **Auto-Updates**: New posts appear smoothly and automatically.

## Development

### Prerequisites
- Node.js & NPM

### Setup
1. Clone the repository.
2. Run `npm install` to install dependencies.

### Build
- Run `npm run dev` for development.
- Run `npm run prod` for production build (minified).

