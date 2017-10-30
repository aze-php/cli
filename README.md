# AZE-CLI

## How to
To launch a local server
```bash
aze serve
```

To launch a local server and open your browser
```bash
aze serve -o
```

## Parameters
To show an help
```bash
aze serve --help
```
List of parameters :
* --host=HOST            host use to serve your application [default: "localhost"]
* --port=PORT            port use to serve your application [default: 80]
* --publicDir=PUBLICDIR  directory containing your public files and your index.php [default: "web"]
* --config=CONFIG        Configuration file to serve your application [default: "config.properties"]
* -o, --open             Open your default browser once the server is launched

## Configuration file
Exemple :
```bash
[server]
host=localhost
port=80
publicDir=web
```