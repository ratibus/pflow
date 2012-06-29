#! /bin/bash

_pflow ()
{
  local cur keywords
  
  COMPREPLY=()
  cur=${COMP_WORDS[COMP_CWORD]}
  
  # The dummy echo is here because of http://clock.co.uk/tech-blogs/bash-completion-problems-with-option-lists-generated-by-php-
  # https://bugs.launchpad.net/ubuntu/+source/php5/+bug/514989
  keywords="$(echo "" | pflow autocomplete "$COMP_LINE")"
  COMPREPLY=( $(compgen -W "$keywords" -- $cur ) )

  return 0
}

complete -F _pflow pflow