#include <stdio.h>
#include <string.h>
#include <errno.h>
#include <stdlib.h>

//#define DEBUG

inline int min(int a, int b){return a<b ? a : b;}

int preprocess(char* source){
    char *p, *q;
    p = source-1;
    q = source; 
    do{
	do ++p; while(*p == ' ' || *p == '\n' || *p == '\t' || *p == '\r');
	*q++ = *p;
    }while(*p);
    return q - source;
}

int readin(const char* filename, char** string){
    FILE *f ;
    int len;

    f = fopen(filename, "r");
    if( !f ){
	fprintf(stderr, "open file `%s' fail: %s\n", filename, strerror(errno));
	return -1;
    }
    fseek(f, 0, SEEK_END);
    len = ftell(f);
    *string = new char[len+1];
    rewind(f);
    fread(*string, len, 1, f);
    fclose(f);
    (*string)[len] = 0;

    len = preprocess(*string);
    return len;
}

int main(int argc, char * argv[]){
    char *src1, *src2;
    int src1_len, src2_len;
    int *space, *last, *now, *tmp;
    int i, j;

    if( argc!=4 ){
	fprintf(stderr, "usage: %s source1 source2 limit\n", argv[0]);
	return 1;
    }

    src1_len = readin(argv[1], &src1);
    src2_len = readin(argv[2], &src2);
    if(src1_len < 0 || src2_len < 0)
	return 1;
#ifdef DEBUG
    fprintf(stderr, "%s %s\n", src1, src2);
#endif

    space = new int[(src2_len+1)*2];
    last = space;
    now = space+src2_len+1;

    for(i=0; i<=src2_len; ++i)
	last[i] = i;

    for(i=1; i<=src1_len; ++i){
	#ifdef DEBUG
	for(j=0; j<=src2_len; ++j)
	    fprintf(stderr, "%d ", last[j]);
	fprintf(stderr, "\n");
	#endif
	now[0] = i;
	for(j=1; j<=src2_len; ++j){
	    if(src1[i-1] == src2[j-1])
		now[j] = last[j-1];
	    else
		now[j] = min(min(last[j-1], last[j]), now[j-1]) + 1;
	}
	tmp = last;
	last = now;
	now = tmp;
    }
#ifdef DEBUG
    for(j=0; j<=src2_len; ++j)
	fprintf(stderr, "%d ", last[j]);
    fprintf(stderr, "\n");
#endif
    printf("%d\n", last[src2_len]);
    if(last[src2_len] > atoi(argv[3]))
	return 1;
    return 0;
}

