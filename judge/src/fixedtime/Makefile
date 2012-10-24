SRCS = time-1.7-Recompute-CPU-usage-at-microsecond-level.patch\
			 time-1.7-retstatfix.patch\
			 time-1.7-ru_maxrss-is-in-kilobytes-on-Linux.patch\
			 time-1.7.tar.gz

time: $(SRCS)
	tar xf time-1.7.tar.gz
	(cd time-1.7; \
	pwd; \
	patch -Np1 -i ../time-1.7-Recompute-CPU-usage-at-microsecond-level.patch; \
	patch -Np1 -i ../time-1.7-retstatfix.patch; \
	patch -Np1 -i ../time-1.7-ru_maxrss-is-in-kilobytes-on-Linux.patch; \
	./configure; \
	make; \
	mv -fv time ../../../bin/; \
	)
