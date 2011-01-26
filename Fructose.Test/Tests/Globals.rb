#TEST EXPECTS:
#10
#10
#20
#123

def a
  test = 123
end

def b
  $test = 123
end

test = 10
$test = 20

puts test
a
puts test

puts $test
b
puts $test