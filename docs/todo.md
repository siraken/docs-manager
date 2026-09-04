# 残っている TODO

コード中に `TODO:` / `FIXME:` コメントで置いてあるもののうち、特に重いもの。

- **MetaMask ログインが安全でない**: ウォレットアドレスは公開情報なので、いまは「知っていれば入れる」認証になっている。nonce への署名と `ecrecover` による検証に置き換える必要がある（`Domain\User\ValueObject\WalletAddress`）
- **NFC の PIN が平文保存**: ログインが平文の完全一致で照合しているため。両方をハッシュ化する場合、シリアル番号は検索キーとして使うので決定的ハッシュが要る（`Domain\User\ValueObject\NfcCredential`）
- **2FA がログインに繋がっていない**: 設定と検証は動くが、ログイン時にコードを要求していない（`Application\Auth\UseCase\LoginWithPasswordUseCase`）
- **ログイン試行のレート制限が無い**: web ルートには throttle が掛かっていない
- **2FA のリカバリコードが無い**: 端末を失うと復旧できない
- **QR コード画像を生成していない**: `otpauth://` URI を手入力してもらう形になっている
