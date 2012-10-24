#!/bin/bash
read -a header 
while read x ; do 
	echo "$x" | tr '\t' '\n' | \
	for col in "${header[@]}" ; do
		read rec
		printf "%9s = '%s'\\n" "$col" "$rec"
	done
	echo "INSERT"
done 
