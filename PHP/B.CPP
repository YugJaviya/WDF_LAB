// #include <iostream>
// using namespace std;
// int main() {
//     int x = 3;

// switch(x % 2) {
//     case 0:
//         x += 2;
//     case 1:
//         x += 3;
//     case 2:
//         x += 2;
// }

// cout << x;
// return 0;
// }

// #include <iostream>
// using namespace std;

// int main() {
//     int a[] = {3, 1, 4, 2};

//     for(int i = 0; i < 3; i++) {
//         if(a[i] > a[i+1]) {
//             a[i] = a[i] - a[i+1];
//             a[i+1] = a[i] + a[i+1];
//         }
//     }

//     cout << a[0] << " " << a[1] << " "
//          << a[2] << " " << a[3];
// }


// #include <iostream>
// using namespace std;
//  int main() {
// int x = 2;

// switch(x) {
//     case 1:
//         cout << 1;

//     case 2:
//         cout << 2;
//         break;

//     case 3:
//         cout << 3;

//     default:
//         cout << 4;
// }

// cout << 5;
// // return 0;
// }

#include <iostream>
using namespace std;

int main() {
    int a = 10;
    int b = --a + a-- - a++ + ++a;
    
    cout << a << " " << b;
    return 0;
}