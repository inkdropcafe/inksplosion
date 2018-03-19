# Welcome to the Grawlix CMS v1.4
The Grawlix CMS is a tool that helps you, a comic artist, publish your own website. But if you’re reading this, then you probably knew that. What you might not know is how to get started. This file will help you along.

# Requirements
The Grawlix CMS requires a web host with PHP5 and MySQL. You’ll need a database on the host before you install the system — check your host’s control panel for details. You will also need a code editor like [Sublime Text](https://www.sublimetext.com/), [Atom](https://atom.io/), or [Notepad++](https://notepad-plus-plus.org/), and FTP app like [FileZilla](https://filezilla-project.org/) or [Transmit](https://panic.com/transmit/). You may also use your webhost's built in FTP or code editing tools.

* [Learn more about the requirements.](http://www.getgrawlix.com/docs/1/ftp) (Comment: This link will need updating)
* [New to FTP? Check out our quick guide.](http://www.getgrawlix.com/docs/1/requirements) (Comment: This link will need updating)
* [Learn about web hosts.](https://nattosoup.blogspot.com/2017/06/self-hosting-your-webcomic-alternatives.html)
* Got an unusual situation? [Tell us what’s up.](https://github.com/Respheal/grawlix/issues)

# Installation
1. If you haven’t already, then create a MySQL database at your webhost.
2. Upload everything that is inside the grawlix-cms-1.4 folder to your web host. There’s usually a public_html or htdocs folder in the FTP account — although some, like Dreamhost, have folders named after their domains. Consult your web host for details.
3. Visit yoursite.com/firstrun.php (where “yoursite.com” is your webcomic’s URL) and follow the prompts.
4. Rename htaccess.txt to .htaccess
5. If your host runs suPHP (Most shared hosts do. You'll get an Internal Server Error if you don't do this and your host runs suPHP), update these lines in the .htaccess:
  
    php_value display_errors 0
    php_value display_startup_errors 0
  
  to
  
    #php_value display_errors 0
    #php_value display_startup_errors 0


Firstrun will give you new code to put into config.php. Upon successful install, you should delete firstrun.php as a security precaution.

# Upgrading from 1.1 or 1.2
1. Make a backup of your site and, if possible, your database.
2. Upload the _system and _admin folders to your site, replacing the old versions.
3. Upload the functions.inc.php and index.php files into your site’s root directory.
4. Copy the new pattern files from any of the included themes into your current theme folder.
5. Go to your admin panel and follow the update prompt at the top of the screen.
6. When it’s done, delete the script _upgrade-to-1.3.php in your _admin folder.
Among other changes and fixes, versions 1.3 and 1.4 make significant changes to static pages. Check out your site’s static pages to be sure everything moved OK.

# More info & support
* [Start from scratch](http://www.thedaemoschronicles.com/grawlix-cms-setup-walkthrough/): Learn about hosting, databases, and setup with this article by Jordan Rodriguez.
* [Read the docs](http://www.getgrawlix.com/docs): Learn more about specific topics in the Grawlix CMS.
* [Ask questions](https://discord.gg/guj9dtV): Seek solutions or share your know-how.
* [Fear no code](https://gumroad.com/l/SACCb): Expunge your apprehension of HTML and CSS with our guide to coding for webcomic artists.
* [Give feedback](https://github.com/inkdropcafe/inksplosion/issues): Something else on your mind? Got a question or comment? Let us know.
Happy publishing!

— the Ink Drop Team
