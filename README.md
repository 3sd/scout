# Concepts

The following concepts are core to Scout.

## Projects

Projects define the code needed to run a CiviCRM site.

They are git repositories with a scout-project.yaml file at the root level.

Projects have one or more instances.

## Instances

Instances are instances of projects.

Once created, they can be installed. Instances can specify other instances of the same project as origins. Instances can pull the database and files from origins. A production site should never have an origin defined as this would allow the production site to be overriden.

Scout instances have a scout-instance.yaml file in their root directory. This file is gitignored.

```scout.yaml
origins:
  prod: "p2:/var/www/3sd"
```

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
