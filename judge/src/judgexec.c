#include <unistd.h>

int main(int argc, char * const argv[]){
    if(argc)
        execv(argv[1], argv + 1);
    return 0;
}
