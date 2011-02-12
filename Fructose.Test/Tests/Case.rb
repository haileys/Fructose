#TEST EXPECTS:
#yep
#yes
#else

a = 7
case a
when 0
when 2
  puts "nope"
when 1
when 2
when 3..8
  puts "yep"
when 7
  puts "late"
else
  puts "no chance"
end

case (a+=1) # tests that the case isn't re-evaluated
when 7
  puts "no"
when 9 
  puts "no"
when 8
  puts "yes"
end

case false
when true
  puts "no"
else
  puts "else"
end