<img src="https://a.slack-edge.com/f85a/img/loading_hash_animation_@2x.gif" alt="Slack animals" />

# Slackbot-AFK
A PHP-based slash command script for setting away status in Slack

## About AFK Slackbot
With traditional instant messaging, you have the ability to set an away message when leaving to provide a little more context of how long you'll be away. When switching to Slack, however, that ability disappears. With Slackbot AFK, though, we can still keep that tradition alive within your organization. Using this document as your guide, you'll be able to see how easy it can be to set up and use the AFK bot to change your Slack status automatically, check other's status messages, and set your own message.

## Setting up the AFK Script
### Setting up the Database

This script utilizes a MySQL database schema that can easily be set to it's initial state using the MySQL .sql file stored in the "AFK-Server" root directory. You'll need to remember the settings for your MySQL server to configure the script settings later.

### Setting up Slack integration

The next step is to set up each of the slash command integrations (there's one for `/afk` and one for `/whereis`): 

1. Open [https://[TEAM-NAME].slack.com/apps/build/custom-integration](https://[TEAM-NAME].slack.com/apps/build/custom-integration)
2. Select "Slash Commands" 
3. Enter `/afk` as the command to create
4. On the next page, enter the URL as `https://[HOSTNAME]/index.php/afk
5. Ensure the method is `POST`
6. Jot down the Token string that is given to you for this command. 

Next, create the slash command for the `/whereis` command: 

1. Open [https://[TEAM-NAME].slack.com/apps/build/custom-integration](https://[TEAM-NAME].slack.com/apps/build/custom-integration)
2. Select "Slash Commands" 
3. Enter `/whereis` as the command to create
4. On the next page, enter the URL as `https://[HOSTNAME]/index.php/whereis
5. Ensure the method is `POST`
6. Jot down the Token string that is given to you for this command. 


### Installing the server script

Installing the server script is easy, simply perform these steps: 

1. Place all of the `AFK-Server` directory contents onto your publicly accessible LAMP-compliant server
2. Open the index.php file and change the `DB::$user` value to be the MySQL user with access to the database 
3. Next, change the `DB::$password` value to be the password for the database user account from step 2
4. Provide the MySQL database name in the `DB::$dbName` value
5. Lastly for DB configuration, provide a value for the `DB::$host` and `DB::$port` variables (if necessary)
5. Locate the lines with variables `afkToken` and `whereisToken` and fill in the token values from Slack for each of those slash commands.
6. That's all, folks! 

You can test the server by sending a `POST` cURL to: 

`https://[SERVER-NAME]/index.php/ping`

You should receive a "PONG" value in the body if the script is accessible. You can now test out the Slash commands inside of Slack.

## Setting Away and Returning
### Setting your own away message

To set your away message, you'll use the following command in Slack:

`/afk away message here`

By replacing "away message here" with your away status message, you'll be marked away and your message will be recorded for anyone else to see when they check your status.

### Returning online

When you wish to return online, and remove your away status message, it couldn't be more easy. Simply open Slack, then type the following command:

`/afk back`

You can also use:

`/afk online`

or:

`/afk clear`

After entering one of these commands your status will automatically be cleared and you'll appear as "online and ready to chat" when your status message is checked.

## Checking Status
### Checking other's status messages

Once your status message has been set, and you're marked as away, you can easily check the current status of any MartianCraft user by typing the following command:

`/whereis username`

Replacing "username" with the Slack username of the person you're inquiring about, you'll be able to see if they're using AFK to update their status; you'll be able to see their current away status if they've set one, along with the time it was last set; and, finally, you'll be able to see if they've cleared their message and are currently available to chat.

## Integrating with Slack
### Setting Away/Online automatically (Optional)

An optional feature of the AFK Slackbot is the ability to have it automatically set your Slack presence as away when creating an away message, and automatically return your presence to auto (Slack's version of "Available") when returning back and clearing out your away status message.

This feature can save you some time; however, you can still use all of the above features without integrating further.

To register for the ability to do this, first open this page: [Slack API Key](https://api.slack.com/web#authentication) to generate an API key for your team. Once you have this long key, copy it to your clipboard, and then type the following command into Slack:

`/afk register token`

Paste in your copied token in place of the "token" text.

## Contributions and Libs Used
The AFK bot is proudly presented and crafted by [Cory Bohon](https://twitter.com/coryb/) and [Beau Bolle](https://twitter.com/BeauGBolle) at [MartianCraft](http://martiancraft.com).

### Libraries Used 
- Slim framework for routing (http://www.slimframework.com)
- MeekroDB (https://github.com/SergeyTsalkov/meekrodb)

## Contributions 
Feel free to add any contributions to this project that you'd like as a Pull Request. The core team reserves the right to accept or reject any Pull Request. 

## Future Goals

* Add endpoint behind authentication called /users.json that will return a list of users and their current away status objects -- this will be used for native apps, or custom implementations for away messages. 
* Create iOS (with watchOS) and macOS menu bar app that will read from the future JSON endpoint implementation to allow for viewing away status. 
* Create an installation script that will allow for easier and more automatic setup
* Crete a script that can be run as a cron job for automatically pulling in new Slack users into the AFK database and setting their current status to NULL
