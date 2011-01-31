#TEST EXPECTS:
#1
#2
#3
#4
#a
#b
#c
#d
#5

for i in 1..4
  puts i
end

for c in 'a'...'e'
  puts c
end

for i in (1..5).to_a - (1...5).to_a
  puts i
end