#TEST EXPECTS:
#c
#c
#b
#b
#a

class A < Error
end

class B < A
end

class C < B
end

begin
  begin
    begin
      raise C.new
    rescue C
      puts :c
    end
  rescue B
    puts :b
  end
rescue A
  puts :a
end

begin
  raise C.new
rescue C
  puts :c
rescue B
  puts :b
rescue A
  puts :a
end

begin
  raise B.new
rescue C
  puts :c
rescue B
  puts :b
rescue A
  puts :a
end

begin
  raise C.new
rescue B
  puts :b
rescue C
  puts :c
rescue A
  puts :a
end

begin
  raise C.new
rescue A
  puts :a
rescue C
  puts :c
rescue B
  puts :b
end