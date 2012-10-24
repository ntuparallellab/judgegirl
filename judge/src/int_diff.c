#include <stdio.h>
#include <assert.h>
#include <stdlib.h>
#include <string.h>

int main(int argc, char **argv){
	FILE *f, *g;
	int a, b;
	int ra, rb;
	if(argc != 3 ){
		fprintf(stderr, "usage %s input1 input2\n", argv[0]);
		return 1;
	}	
	if( (f=fopen(argv[1], "r")) == NULL)
		fprintf(stderr, "fail opening %s\n", argv[1]);
	if( (g=fopen(argv[2], "r")) == NULL)
		fprintf(stderr, "fail opening %s\n", argv[2]);
	if( f == NULL || g == NULL )
		exit (1);
	while(1){
		ra = fscanf(f, "%d", &a);
		rb = fscanf(g, "%d", &b);
		if( ra == EOF || rb == EOF ){
			exit((ra == EOF && rb == EOF)?0:1);
		}
		if( a != b ){
			exit(1);
		}
	}
	assert(0);
}
