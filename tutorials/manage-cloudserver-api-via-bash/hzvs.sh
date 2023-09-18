#!/bin/bash
#
#   On the server, the user which is login using ssh MUST have the right to shutdown via sudo
#
#   For example, on a Debian server, to allow all users in adm group, adding following line in visudo will do the trick
#
#   %adm ALL:NOPASSWD: /usr/sbin/shutdown

#   For novnc.com, at date of this script, command will look like 
#   host=web-console.hetzner.cloud port=443 path=/?server_id=12345678&token=<token>
#

# 0=quite 1=verbose 2=to file $mytmp/$myserver
verbose=0

# Hetzner API key for server, prefix is a user identifier, can be the hostname
# second value is the server ID, third one the ssh options and parameters to connect
your_host1_APIKEY=("1234567890true0987654321FALSENY8WRZTQqIteRLDcCzsM2tp4Q9CXzzfcLFA" "012345" "-p 12345 holu1@2001:db8:1234::1")
your_host2_APIKEY=("1234567890true0987654321FALSED1RlaFeNlsSJspC1zHE7i1fi0qWsSB5TCAr" "012346" "-p 22 holu22@198.51.100.1")

# Hetzner link to metadata
hzmeta="curl -s http://169.254.169.254/hetzner/v1/metadata"

# Location of local ssh binaries
myssh=$(command -v ssh)

# novnc URL & port to use to connect
novnc="https://novnc.com/noVNC/vnc.html"
novncport=443

# which browser to use. You can add --private (FF) or --incognito (Chrome) for private navigation
#browser="$(which gnome-www-browser) --new-window"          # default system browser
browser="$(which x-www-browser) --new-window"              # default system browser
#browser="$(which firefox) --new-window"                    # Firefox software packet
#browser="$(which firefox-esr) --new-window --private"      # Firefox software packet
#browser="$(which chromium) --new-window"                   # chromium Debian software packet
#browser="$(which chrome) --new-window --incognito"         # Google chrome software packet
#browser="/opt/google/chrome/chrome --new-window"           # on Debian/Ubuntu downloaded from google

# if VM is off should we automatically start it
autoON=0

# working directory
mytmp=/var/tmp

#
# Various functions
#
ishostup() {
  # We ping $1 (server IP) with $2 second timeout
  # echo return empty if host is up
  #
  echo $(ping -w$2 $1|grep "0 received")
}

echotext() {
  # Return formatted text
  #
  echo && echo $1 && echo
}

apisend() {
  # Send api cmd
  #
  curl -X POST -s -o $mytmp/$myserver \
  -H "Authorization: Bearer $1" \
  "https://api.hetzner.cloud/v1/servers/$2/actions/$3" 
  [ $verbose -gt 0 ] && cat $mytmp/$myserver
}

poweron() {
  # Start server
  #
  [ "x$(ishostup $myip 2)" == "x" ] && echotext "Host is already running, no ACTION taken" && return
  apisend ${!mykey} ${!myid} poweron
}

poweroff() {
  # Shutdown server
  #
  [ "x$myssh" = "x" ] && echotext "FAILURE - ssh binary not found on this station, EXIT" && return
  [ "x$(ishostup $myip 2)" != "x" ] && echotext "Host is not up, no ACTION taken" && return

  # gracefully stop the server using shutdown
  [ $verbose -eq 0 ] && sshoutput="-q" || sshoutput=""
  myshutdown=$($myssh $sshoutput $1 whereis shutdown|cut -d " " -f2)    # connect a first time to the server to get the path & the binary
  [ $verbose -eq  1 ] && echo "shutdown@$myserver is located at $myshutdown"
  if [ "$(echo $myshutdown|grep shutdown)" != "" ] 
  then
    [ $verbose -eq  1 ] && echotext '$myssh $1 "$myshutdown -h now"'
    $myssh $sshoutput $1 "sudo $myshutdown -h now"                      # connect a second time and run shutdown command
  else
    echotext "FAILURE - ssh error or \"shutdown\" binary not found on $myserver@$myip, EXIT" && return
  fi
}

reboot() {
  # Reboot server
  #
  [ "x$myssh" = "x" ] && echotext "FAILURE - ssh binary not found on this station, EXIT" && return
  [ "x$(ishostup $myip 2)" != "x" ] && echotext "Host is not up, ABORTING" && return

  # gracefully reboot the server using shutdown
  [ $verbose -eq 0 ] && sshoutput="-q" || sshoutput=""
  myshutdown=$($myssh $sshoutput $1 whereis shutdown|cut -d " " -f2)    # connect a first time to the server to get the path & the binary
  [ $verbose -eq 1 ] && echo "shutdown@$myserver is located at $myshutdown"
  if [ "$(echo $myshutdown|grep shutdown)" != "" ] 
  then
    [ $verbose -eq 1 ] && echotext '$myssh $1 "$myshutdown -h now"'
    $myssh $sshoutput $1 "sudo $myshutdown -r now"                      # connect a second time and run shutdown command
  else
    echotext "FAILURE - ssh error or \"shutdown\" binary not found on $myserver@$myip, EXIT" && return
  fi
}

askon() {
  # Ask user for action. Text to display is sended as parameter $1
  #
  local answer="N"
  [ $verbose -eq 0 ] && echo
  read -n 1 -p "$1" answer 
  answer=$(echo $answer|tr [:lower:] [:upper:])
  [ "$answer" != "Y" ] && echotext "Aborted by user, EXIT" && exit 0
}

console() {
  # Get console parameters. If "run" is sended in cmd line, we connect immediately to the server console
  #
  if [ "x$(ishostup $myip 2)" != "x" ]; then
    if [ $autoON -eq 1 ]; then poweron; else
      if [ "x$myrun" != "x" ]; then poweron; else
        askon "Server $myserver is powered OFF. Should I start it (y/N)? "
      fi
    fi
  fi

  apisend ${!mykey} ${!myid} request_console
  mywss=$(cat $mytmp/$myserver|tr -d '\n'|tr -s " "|cut -d , -f1|cut -d \  -f3|tr -d \")
  mypwd=$(cat $mytmp/$myserver|tr -d '\n'|tr -s " "|cut -d , -f2|cut -d \"  -f4)
  novnchost=$(echo $mywss|cut -d \/ -f3)
  novncpath=$(echo $mywss|cut -d \/ -f4|sed 's/&/\%26/g')
  mynovnc="$browser $novnc?host=$novnchost&autoconnect&password=$mypwd&port=$novncport&path="/$novncpath
  [ $verbose -gt 0 ] && echo && echo $mynovnc

  command $mynovnc
}

getmeta() {
  # Get meta parameters
  #
  if [ "x$(ishostup $myip 2)" == "x" ]; then
    [ $verbose -eq 1 ] && echotext '$myssh $1 " $hzmeta"'
    $myssh $1 "$hzmeta" > $mytmp/$myserver
    cat $mytmp/$myserver|grep -B 999 "^region:"
  else
     echotext "Host $myserver is DOWN"
  fi
}

status() {
  # VM status
  #
  [ "x$(ishostup $myip 2)" == "x" ] && echotext "Host $myserver is UP" || echotext "Host $myserver is DOWN"
}

usage() {
  # Script usage
  #
  echo
  echo "Usage: $(basename $0) <server> <on|off|status|reboot|apcireboot|force|console|meta>"
}

# Main program
#

if [ "x$(echo $1|grep "\-h")" != "x" -o "x$1" = "x" -o "x$2" = "x" ]; then
  usage
  exit 0
fi

myserver=$1
mykey=$1_APIKEY[0]

[ $verbose -eq  0 ] && cmdoutput="-s -o /dev/null" || cmdoutput=

if [ "x${!mykey}" != "x" ]
then
  myid=$1_APIKEY[1]
  mysshopt=$1_APIKEY[2]
  myip=$(echo ${!mysshopt}|cut -d \@ -f2)
  myrun=$(echo $1$2$3$4|tr [:upper:] [:lower:]|grep run)
  [ $verbose -eq 1 ] && echo "API key = ${!mykey}" && echo "ID .... = ${!myid}" && echo "ssh cmd = ${!mysshopt}" && echo

  case "$(echo $2|tr [:upper:] [:lower:])" in
    "on")
      poweron
      ;;
    "off")
      poweroff "${!mysshopt}"
      ;;
    "status")
      status
      ;;
    "force")
      apisend ${!mykey} ${!myid} poweroff
      ;;
    "apcireboot")
      apisend ${!mykey} ${!myid} reboot
      ;;
    "reboot")
      reboot "${!mysshopt}"
      ;;
    "console")
      console $3
      ;;
    "meta")
      getmeta "${!mysshopt}"
      ;;
    *)
      echo
      echo "=> $2 is not a known command"
      usage
      ;;
  esac
else
  echo "API key NOT existing, please verify server parameters <$1>"
fi

exit 0
