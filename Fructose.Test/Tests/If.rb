#TEST EXPECTS:
#yes
#no
#yes
#no

if true
  puts "yes"
else
  puts "no"
end

if false
  puts "yes"
elsif nil
  puts "maybe"
elsif 3 > 1
  puts "no"
end

if "".empty?
  puts "yes"
else
  puts "no"
end

if not ""
  puts "yes"
else
  puts "no"
end