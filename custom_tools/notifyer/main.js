var path = require("path");
var parser = require('rss-parser');
var Datastore = require('nedb');
var db = new Datastore({filename : __dirname + '/jobs'});
var forums = [
  'https://drupal.ru/taxonomy/term/2058/feed',
  'https://drupal.ru/taxonomy/term/2056/feed',
  'https://drupal.ru/taxonomy/term/62/feed',
  'https://freelance.ru/rss/feed/project/s.4.f.316'
]
const TelegramBot = require('node-telegram-bot-api');
const token = '514695030:AAHUIGep9c1kKkRVflwGi0DzvtLu3Z_zpys';
const chat_id = '@drupalclients';
const bot = new TelegramBot(token);


db.loadDatabase();

forums.forEach(function(forum){
  parser.parseURL(forum, function(err, parsed) {
  parsed.feed.entries.forEach(function(entry) {
    db.find({link: entry.link}, function(err, docs){
      if(docs == '') {
        db.insert({title: entry.title, link: entry.link, date: entry.isoDate});
        bot.sendMessage(chat_id, entry.title + ' ' + entry.link);
      }
    });
  })
})
});