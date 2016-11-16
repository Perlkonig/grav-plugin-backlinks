# Backlinks Plugin

The **Backlinks** Plugin is for [Grav CMS](http://github.com/getgrav/grav). For each page in your site, it records all other pages that point to it. In wikis, these are commonly called "backlinks."

## Installation

Installing the Backlinks plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install backlinks

This will install the Backlinks plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/backlinks`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `backlinks`. You can find these files on [GitHub](https://github.com/aaron-dalton/grav-plugin-backlinks) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/backlinks
	
> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) and the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) to operate.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/backlinks/backlinks.yaml` to `user/config/plugins/backlinks.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
datafile: 'backlinks.yaml'  # relative to `user/data`
```

* The `enabled` flag turns the plugin off and on.

* `datafile` points to a file relative to the `user/data` folder where you want the backlinks data to live.

## Usage

**Describe how to use the plugin.**

