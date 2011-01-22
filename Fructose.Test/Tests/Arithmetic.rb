#TEST EXPECTS:
#true

puts ((((9 % 6 & 5 * -3 + 2 - 1 / 2) << 2) <=> 13) ^ 73).abs.next[1].odd?