#!/bin/bash
mail_suffix="ntu.edu.tw";
for i ; do
	user="${i/info_/}"
	(
	echo "Subject: [C2011] Judgegirl System Account Information for $user" 
	echo "From: C2011TA <b97044@csie.ntu.edu.tw>"
	echo "To: $user@$mail_suffix"
	echo "(IF you've alreay recieved this e-mail, please ignore this message)"
	cat "$i"
	) | sendmail "$user@$mail_suffix"
done
