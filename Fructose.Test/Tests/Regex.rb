#TEST EXPECTS:
#0
#nil
#nil
#1
#false
#true
#true
#true
#true
#5
#,
#true
#Hello
#ello
#1
#6
#1bc
#111
#123
#2

regex = /[a-z]{3}/

puts regex === "hello"
puts (regex =~ "h-el-lo").inspect

puts /\d/.match("test").inspect
puts /\d/.match("123").size

puts /test/.casefold?
puts /test/i.casefold?
puts /test/xi.casefold?
puts /test/ix.casefold?
puts /test/mix.casefold?

md = /^(([a-z])([a-z]+))(?<punc>[^a-z])/i.match "Hello, World!"
puts md.size

puts md[:punc]
puts md[0].include? md[1]
puts md[1]
puts md[3]
puts md.begin(3)
puts md.end(:punc)

puts "abc".sub(/./, 1)
puts "abc".gsub(/./, 1)

i = 0
n = "abc".gsub /./ do |m|
  i += 1
end
puts n

"123" =~ /.(.)(.)/
puts $1