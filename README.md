# Quick start

Create a local site at http://example

```
scout create-project /var/www/example
cd /var/www/example
scout install-instance
```

# Projects

Projects define the code needed to run a CiviCRM site.

They are git repositories with a `scout-project.json` file at the root level.

Projects can be created with the `create-project` command.

Projects have one or more instances.

# Instances

An instance of a project is a git checkout of that project.

Instances can be installed with the `install-instance` command. Installing an instance runs the CMS and CiviCRM installation scripts (which create the relevant settings/configuration files and create and populate the databases) and attempts to configure your web-server to serve the project.

Different instances of the same project can be related to each other in order for you to synchronise the database and files from one instance to another.

Scout tries to make it easy to synchronise from one site to another, but hard to unintentionally overwrite production data. Synchronisation happens via a 'pull' mechanism, i.e. from a remote site to a local site and instances can only synchronise from pre-defined origins.

For example a scout project can have a production instance with no origins defined and a development instance with the production instance defined as an origin. It this set up, one can synchronise databases and files from the production instance to the development instance but one cannot accidentally pull from the development to the production instance as the development instance is not defined as an origin on the production instance. Hence the chances of accidentally overwriting production data are reduced.

Scout instances have a scout-instance.json file in their root directory, which defines related instances.

```scout.yaml
origins:
  prod: "p2:/var/www/3sd"
```

# Synchronisation


## Controllers

A scout controller is anyone that looks after scout instances.

# Commands

A list of scout commands

## Project commands

### Create a project

`scout create-project [name]`

option            | default | status
----------------- | ------- | ------
--civicrm-version | latest  | DONE
--cms-version     | latest  | TODO
--cms             | drupal  | TODO

Create a project. Takes various options such as the CiviCRM version, CMS type, and CMS version.

## Instance Commands

All instance commands take the option -i to specify the path to the instance to be operated on. If -i is not specified, scout will search recursively up the path for an instance to work with, i.e. search recursively for a scout-project.yaml

### Status report

`scout status`

Returns a json object with the status of an instance and any errors

What project does it belong to (what is the git repo) It is installed (available at the URL specified) What version of CiviCRM is it running What CMS is it running? What version of the CMS is it running? What origins does it have? Reports any errors, or issues with the instance (and the project that it is an instance of).

### Create an instance of a project

`scout init [project-repo] [path]`

Creates an instance of [project] in [path].

### Install an instance

`scout install`

### Pull DB and files from an origin

`scout sync [origin]`

Will import DB and files from the origin to the current instance.

Note this function will check to see if the remote and local code are at the same commit before attempting to pull in the remote database and files. By default, it will only If not, it will report a warning.

## Update CiviCRM and the CMS

`scout update`

Downloads the latest version of CiviCRM and the host CMS and performs any necessary database updates.

## Update CiviCRM and the CMS database only

`scout update-db`

Downloads the latest version of CiviCRM and Drupal and

## Update CiviCRM and the CMS code only

`scout update-code`

# Implementation

Calling a scout command typically invokes a PHP script which generates a list of bash commands to be executed.

`scout clone [source-instance] [dest-instance]`
