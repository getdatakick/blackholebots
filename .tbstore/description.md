# Blackhole for Bad Bots

This is very simple free module for prestashop and thirtybees platforms. It is based on a very simple [idea](https://perishablepress.com/blackhole-bad-bots/):

1. you instruct all robots visiting your website NOT to open specific url
2. this module will add **hidden** link from all pages on your website to this forbidden page. This link is perfectly visible to
all robots, but normal visitors will not notice it at all (without looking into web page source code)
3. when anyone access this forbidden page, his IP address will be immediately added to blacklist
4. blacklisted visitor are forbidden from viewing content from your website
5. shop administrator is notified about new entries to blacklist. They will receive email with WHOIS information about the visitor - his IP address, location, network, etc.

And that's it. This trap will not affect any *good* robots who are following ```robots.txt``` directives.
On other hand, all *bad bots* and crawlers will be eventually trapped and forbidden from ever collecting information
from your site again.

## Module activation

1. edit ```robots.txt``` file in the root directory

Before you install this module, you need to edit your ```robots.txt``` file, and add following two lines

```
User-agent: *
Disallow: /blackhole/
```

2. install module

3. optionally, you can test it by navigating to ```http://www.yourdomain.com/blackhole/```. You should be banned from your own site. To lift the ban, reset module from your back office.

## Moderation

At the moment there isn't any UI to manage blacklist. If you want to remove some IP address
from blacklist, you have to make manual changes in database table called ```PREFIX_blackholebots_blacklist```

## Compatibility

- thirtybees
- prestashop 1.6.x.x
- prestashop 1.7.x.x

## Author

Petr Hučík - [datakick](https://www.getdatakick.com)
