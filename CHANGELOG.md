# Change Log

## 1.2.0 - 2019-06-05

Maintenance release with some cleanups in case anyone is still using this.

## 1.1.0 - 2016-05-05

### Deprecated

- Core plugins and plugin client, moved to [client-common](https://github.com/php-http/client-common)
- Logger plugin, moved to [logger-plugin](https://github.com/php-http/logger-plugin)
- Cache plugin, moved to [cache-plugin](https://github.com/php-http/cache-plugin)
- Stopwatch plugin, moved to [stopwatch-plugin](https://github.com/php-http/stopwatch-plugin)


## 1.0.1 - 2016-01-29

### Changed

 - Set the correct header for the Content-Length when in chunked mode.

## 1.0.0 - 2016-01-28

### Added

 - New Header plugins (see the documentation)
 - New AddHost plugin (add or replace a host in a request)

### Changed

- Using array options for DecoderPlugin, RedirectPlugin and RetryPlugin

### Fixed

- Decoder plugin no longer sends accept header for encodings that require gzip if gzip is not available


## 0.1.0 - 2016-01-13

### Added

- Initial release
