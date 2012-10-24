UTILS = checkedit double_diff int_diff str_diff 
JEXEC = judgexec
CFLAGS += -O2
CXXFLAGS += -O2

all: tools time

tools: $(UTILS) $(JEXEC)
	mv -fv $(UTILS) ../bin
	mv -fv $(JEXEC) ../

time: 
	cd fixedtime && make
