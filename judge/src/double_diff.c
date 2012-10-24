#include <stdio.h>
#include <assert.h>
#include <stdlib.h>
#include <math.h>
#include <string.h>

int main(int argc, char **argv){
	FILE *f, *g;
	double delta ;
	double a, b;
	int ra, rb;
	if(argc != 4 ){
		fprintf(stderr, "usage %s input1 input2 error\n", argv[0]);
		return 1;
	}	
	if( (f=fopen(argv[1], "r")) == NULL)
		fprintf(stderr, "fail opening %s\n", argv[1]);
	if( (g=fopen(argv[2], "r")) == NULL)
		fprintf(stderr, "fail opening %s\n", argv[2]);
	if( f == NULL || g == NULL )
		exit (1);
	delta = strtod( argv[3], NULL );
	while(1){
		ra = fscanf(f, "%lf", &a);
		rb = fscanf(g, "%lf", &b);
/*
		if(ra != EOF) fprintf(stderr, "%lf ", a);
		if(rb != EOF) fprintf(stderr, "%lf ", b);
		if(ra!=EOF || rb!=EOF) fprintf(stderr, "\n");
*/
		if( ra == EOF || rb == EOF ){
			exit((ra == EOF && rb == EOF)?0:1);
		}
		if( isnan(a) || isinf(a) || isnan(b) || isinf(b) )
			exit(1);
		if(fabs ( a - b ) > delta ){
			exit(1);
		}
	}
	assert(0);
}
