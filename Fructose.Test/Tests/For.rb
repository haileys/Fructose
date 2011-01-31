#TEST EXPECTS
#1
#2
#3
#a1
#b2
#c3

for n in [1,2,3]
  puts n
end

for k, v in { :a => 1, :b => 2, :c => 3 }
  puts "#{k}#{v}"
end