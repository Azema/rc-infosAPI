guard 'phpunit', :tests_path => 'tests', :cli => '--bootstrap tests/bootstrap.php --colors' do
  # Watch tests files
  watch(%r{^.+Test\.php$})

  # Watch library files and run their tests
  watch(%r{^Model/(.+)\.php}) { |m| "tests/Model/#{m[1]}Test.php" }
end
