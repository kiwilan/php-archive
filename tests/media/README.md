# Metadata for the media tests

## Archives

```bash
rm -f archive.zip archive.7z archive.tar archive.tar.gz archive.tar.bz2 archive.tar.xz archive.rar
```

```bash
zip archive.zip ./archive/**/*
7z a archive.7z ./archive
tar -cvf archive.tar ./archive
tar -czvf archive.tar.gz ./archive
tar -cjvf archive.tar.bz2 ./archive
tar -cJf archive.tar.xz ./archive
rar a archive.rar ./archive
rar a cba.rar ./cba
zip cba.zip ./cba/**/*
7z a cba.7z ./cba
mv cba.rar cba.cbr
mv cba.zip cba.cbz
mv cba.7z cba.cb7
```

### With password

````bash
zip -er archive-password.zip archive
7z a -ppassword archive-password.7z archive
tar -czf - archive | 7z a -si archive-password.tgz -p"password"
rar a -ppassword archive-password.rar archive
```


## PDF

```bash
brew install exiftool
````

```bash
exiftool \
  -Author="Vue Mastery" \
  -CreationDate="2019:10:18 00:00:00" \
  -Creator="Vue Mastery PDF" \
  -Keywords="Vue3,Vue,composition-api" \
  -Producer="Vue" \
  -Subject="Vue3 Composition API" \
  -Title="Vue3 Composition API" \
  "./pdf-example.pdf"
```

```bash
exiftool -a -G1 example.pdf
```
