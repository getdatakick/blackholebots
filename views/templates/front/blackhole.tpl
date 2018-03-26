<html>
  <head>
    <title>Blackhole</title>
    <link rel="stylesheet" type="text/css" href="{$css}" />
  </head>
  <body>
    <div class="blackhole">
      <h1>You have fallen into a trap!</h1>
      <p>
        This site's <a href="/robots.txt">robots.txt</a> file explicitly forbids your presence at this location.
        The following Whois data will be reviewed carefully. If it is determined that you suck, you will be banned from this site.
        If you think this is a mistake, <em>now</em> is the time to <a href="/contact/">contact the administrator</a>.
      </p>
      <h3>Your IP Address is {$ip}</h3>
      <pre>
      WHOIS Lookup for {$ip}

      {$whois}
      </pre>
    </div>
  </body>
</html>
