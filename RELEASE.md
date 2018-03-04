# Release Workflow

Print this document.

Specify the release version.

```
VERSION=1.1.0
```

## Issues

Check issues at https://github.com/nbuchwitz/icingaweb2-module-map/milestones

## Version

Update the version number in the following file:

* [module.info](module.info): Version: (.*)

Example:

```
sed -i "s/Version: .*/Version: $VERSION/g" module.info
```

## Changelog

Ensure to have [github_changelog_generator](https://github.com/skywinder/github-changelog-generator)
installed and set the GitHub token to avoid rate limiting.

```
github_changelog_generator -u nbuchwitz -p icingaweb2-module-map --future-release=$VERSION
```

## Git Tag

Commit these changes to the "master" branch:

```
$ git commit -v -a -m "Release version $VERSION"
```

Create a signed tag (tags/v<VERSION>) on the "master" branch.

```
$ git tag -m "Version $VERSION" v$VERSION
```
Push the tag.

```
$ git push --tags
```

# External Dependencies

## Release Tests

* Provision the vagrant boxes and pull the master in `/usr/share/icingaweb2/modules/map`

Example:

```
$ git clone https://github.com/Icinga/icinga-vagrant.git
$ cd icinga-vagrant/standalone
$ vagrant up
$ vagrant ssh -c "cd /usr/share/icingaweb2/modules/map && sudo git pull"
```

## GitHub Release

Create a new release for the newly created Git tag.
https://github.com/nbuchwitz/icingaweb2-module-map/releases

Note: A new GitHub release will be synced by Icinga Exchange automatically.

## Announcement

* Twitter (highlight @icinga, use hashtags #icinga #map #monitoringlove)
* Forum: https://monitoring-portal.org/t/map-module-for-icinga-web-2/101

# After the release

* Close the released version at https://github.com/nbuchwitz/icingaweb2-module-map/milestones
