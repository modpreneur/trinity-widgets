#!/bin/bash sh

composer update

phpunit

phpstan analyse Controller/ DataFixtures/ DependencyInjection/ Entity/ Event/ Exception/ Form/ Tests/ Twig/ Widget/ --level=4

tail -f /dev/null