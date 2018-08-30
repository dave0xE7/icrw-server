wget https://github.com/EdJoWa/InterCrone-Wallets/raw/master/InterCrone_Linux_deb9.zip
unzip InterCrone_Linux_deb9.zip
rm InterCrone_Linux_deb9.zip
sudo apt-get install -y build-essential libboost-all-dev libcurl4-openssl-dev libdb5.1-dev libdb5.1++-dev qt5-default qttools5-dev-tools qt-sdk make qrencode libqrencode-dev
sudo apt-get install -y libminiupnpc-dev libdb5.3-dev libdb5.3++-dev
sudo ln -s /usr/lib/x86_64-linux-gnu/libminiupnpc.10.so /usr/lib/x86_64-linux-gnu/libminiupnpc.so.17
