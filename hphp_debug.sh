#!/bin/sh

echo "ocP: Running..."
sudo hhvm -m server  -v "Server.SourceRoot=`pwd`" -v Server.DefaultDocument=index.php -v Log.Level=Info -v Log.InjectedStackTrace=true -v Log.Header=on --config ./ocp.hdf
