#TEST EXPECTS:
#1
#2
#3
#4
#1
#2
#3
#4
#foo
#bar
#1
#2
#3


x = 1
# both styles of while
while x < 5
  puts x
  x = x + 1
end

x = 1
# both styles of while
while x < 5 do
  puts x
  x = x + 1
end

begin
  puts :foo
end while false

begin
  puts :bar
end until true

x = 1
until x == 4
  puts x
  x = x + 1
end 