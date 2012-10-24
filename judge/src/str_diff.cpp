#include<iostream>
#include<fstream>
#include<string>
#include<cstdlib>
using namespace std;
int main(int argc, char**argv){
  ifstream f1(argv[1]), f2(argv[2]);
  if( !f1.is_open() || !f2.is_open() ){
    if( !f1.is_open() )
      cerr << "Error opening " << argv[1] << endl;
    if( !f2.is_open() )
      cerr << "Error opening " << argv[2] << endl;
    exit(1);
  }
  string s1, s2;
  while(1){
    f1 >> s1 ; f2 >> s2 ;
    if( !f1 || ! f2 )
      exit( !f1&&!f2 ? 0 : 1 );
    if( s1 != s2 )
      exit(1);
  }
}
