#TEST EXPECTS:
#tester
#test
#testing
#testing 2: test harder
#testing with a vengeance

def optional(x, y="er")
  puts x + y
end

optional "test"
optional "test", ""

def splats(x, y="ing", *z)
  puts x + y + z.join
end

splats "test"
splats "test", "ing 2: test harder"
splats "test", "ing", " w", "i", "t", "h ", "a vengeance"