#TEST EXPECTS:
#42
#720
#from php
#from fructose

require :phpcall
require 'Tests/PHPCall.php'

puts phpcall(:foo)
puts phpcall(:fact, 6)

phpobj = phpcall :myClassFactory
phpobj.attr_set :msg, "from php"
phpobj.call :foobar
phpobj.attr_set :msg, "from fructose"
puts phpobj.attr :msg